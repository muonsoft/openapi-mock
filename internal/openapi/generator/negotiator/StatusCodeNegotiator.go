package negotiator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
	"math"
	"net/http"
	"strconv"
	"swagger-mock/pkg/logcontext"
)

type StatusCodeNegotiator interface {
	NegotiateStatusCode(request *http.Request, responses openapi3.Responses) (key string, code int, err error)
}

func NewStatusCodeNegotiator() StatusCodeNegotiator {
	return &statusCodeNegotiator{}
}

type statusCodeNegotiator struct{}

func (negotiator *statusCodeNegotiator) NegotiateStatusCode(request *http.Request, responses openapi3.Responses) (key string, code int, err error) {
	minSuccessCode := math.MaxInt32
	minSuccessCodeKey := ""
	hasSuccessCode := false
	minErrorCode := math.MaxInt32
	minErrorCodeKey := ""
	hasErrorCode := false

	for key := range responses {
		code, err := negotiator.parseStatusCode(request.Context(), key)
		if err != nil {
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
		return minSuccessCodeKey, minSuccessCode, nil
	}
	if hasErrorCode {
		return minErrorCodeKey, minErrorCode, nil
	}

	return "", http.StatusInternalServerError, errors.Wrap(ErrNoMatchingResponse, "[statusCodeNegotiator] failed to negotiate response")
}

func (negotiator *statusCodeNegotiator) parseStatusCode(ctx context.Context, key string) (int, error) {
	var err error
	code := 0

	if key == "default" {
		code = http.StatusInternalServerError
	} else {
		code, err = strconv.Atoi(key)
		if err != nil {
			logger := logcontext.LoggerFromContext(ctx)
			logger.Warnf(
				"[statusCodeNegotiator] response with key '%s' is ignored: "+
					"key must be a valid status code integer or equal to 'default'",
				key,
			)
		}
	}

	return code, err
}
