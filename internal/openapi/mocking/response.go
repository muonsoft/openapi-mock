package mocking

import (
	"context"
	"math"
	"net/http"
	"regexp"
	"strconv"
	"strings"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/getkin/kin-openapi/routers"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	"github.com/pkg/errors"
)

type Response struct {
	StatusCode  int
	ContentType string
	Data        interface{}
}

func NewResponseMocker(contentGenerator ContentGenerator) *ResponseMocker {
	return &ResponseMocker{contentGenerator: contentGenerator}
}

type ContentGenerator interface {
	GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error)
}

type ResponseMocker struct {
	contentGenerator ContentGenerator
}

func (mocker *ResponseMocker) GenerateResponse(request *http.Request, route *routers.Route) (*Response, error) {
	response, statusCode, err := SelectResponse(request, route.Operation.Responses)
	if err != nil {
		return nil, errors.WithMessage(err, "[ResponseMocker] failed to negotiate response")
	}

	contentType := NegotiateContentType(request, response)
	contentData, err := mocker.contentGenerator.GenerateContent(request.Context(), response, contentType)
	if err != nil {
		return nil, errors.WithMessage(err, "[ResponseMocker] failed to generate response data")
	}

	return &Response{
		StatusCode:  statusCode,
		ContentType: contentType,
		Data:        contentData,
	}, nil
}

func SelectResponse(
	request *http.Request,
	responses openapi3.Responses,
) (response *openapi3.Response, code int, err error) {
	minSuccessCode := math.MaxInt32
	minSuccessCodeKey := ""
	hasSuccessCode := false
	minErrorCode := math.MaxInt32
	minErrorCodeKey := ""
	hasErrorCode := false

	for key := range responses {
		code, err := parseStatusCode(key)
		if err != nil {
			logcontext.Warnf(
				request.Context(),
				"[SelectResponse] response with key '%s' is ignored: "+
					"key must be a valid status code integer or equal to 'default', "+
					"'1xx', '2xx', '3xx', '4xx' or '5xx'",
				key,
			)

			continue
		}

		if code >= 200 && code < 300 && code < minSuccessCode {
			hasSuccessCode = true
			minSuccessCode = code
			minSuccessCodeKey = key
		} else if code < minErrorCode {
			hasErrorCode = true
			minErrorCode = code
			minErrorCodeKey = key
		}
	}

	if hasSuccessCode {
		return responses[minSuccessCodeKey].Value, minSuccessCode, nil
	}
	if hasErrorCode {
		return responses[minErrorCodeKey].Value, minErrorCode, nil
	}

	return nil, http.StatusInternalServerError, errors.Wrap(ErrNoMatchingResponse, "[SelectResponse] failed to negotiate response")
}

var rangeDefinitionPattern = regexp.MustCompile("^[1-5]xx$")

func parseStatusCode(key string) (code int, err error) {
	key = strings.ToLower(key)

	switch {
	case key == "default":
		code = http.StatusInternalServerError
	case rangeDefinitionPattern.MatchString(key):
		code, _ = strconv.Atoi(string(key[0]))
		code *= 100
	default:
		code, err = strconv.Atoi(key)
	}

	return code, err
}
