package mocking_test

import (
	"context"
	"errors"
	"net/http"
	"net/http/httptest"
	"testing"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/openapi/mocking"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	"github.com/sirupsen/logrus"
	"github.com/sirupsen/logrus/hooks/test"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/require"
)

func TestSelectResponse_OnlySuccessfulResponses_ResponseWithMinCodeReturned(t *testing.T) {
	responses := openapi3.Responses{
		"204": &openapi3.ResponseRef{Value: openapi3.NewResponse()},
		"299": &openapi3.ResponseRef{Value: openapi3.NewResponse()},
		"201": &openapi3.ResponseRef{Value: openapi3.NewResponse()},
	}
	request := httptest.NewRequest("", "/", nil)

	response, code, err := mocking.SelectResponse(request, responses)

	require.NoError(t, err)
	assert.Equal(t, responses["201"].Value, response)
	assert.Equal(t, http.StatusCreated, code)
}

func TestSelectResponse_OnlyErrorResponses_ResponseWithMinErrorCodeReturned(t *testing.T) {
	responses := openapi3.Responses{
		"500": &openapi3.ResponseRef{Value: openapi3.NewResponse()},
		"301": &openapi3.ResponseRef{Value: openapi3.NewResponse()},
		"400": &openapi3.ResponseRef{Value: openapi3.NewResponse()},
	}
	request := httptest.NewRequest("", "/", nil)

	response, code, err := mocking.SelectResponse(request, responses)

	require.NoError(t, err)
	assert.Equal(t, responses["301"].Value, response)
	assert.Equal(t, http.StatusMovedPermanently, code)
}

func TestSelectResponse_EmptyResponses_Error(t *testing.T) {
	responses := openapi3.Responses{}
	request := httptest.NewRequest("", "/", nil)

	response, code, err := mocking.SelectResponse(request, responses)

	assert.True(t, errors.Is(err, mocking.ErrNoMatchingResponse))
	assert.EqualError(t, err, "[SelectResponse] failed to negotiate response: no matching response found")
	assert.Nil(t, response)
	assert.Equal(t, http.StatusInternalServerError, code)
}

func TestSelectResponse_InvalidResponseKey_ResponseWithMinCodeReturnedAndErrorLogged(t *testing.T) {
	responses := openapi3.Responses{
		"200":         &openapi3.ResponseRef{Value: openapi3.NewResponse()},
		"unsupported": &openapi3.ResponseRef{Value: openapi3.NewResponse()},
	}
	logger, hook := test.NewNullLogger()
	ctx := logcontext.WithLogger(context.Background(), logger)
	request := httptest.NewRequest("", "/", nil)
	request = request.WithContext(ctx)

	response, code, err := mocking.SelectResponse(request, responses)

	require.NoError(t, err)
	assert.Equal(t, responses["200"].Value, response)
	assert.Equal(t, http.StatusOK, code)
	assert.Equal(t, logrus.WarnLevel, hook.LastEntry().Level)
	assert.Equal(
		t,
		"[SelectResponse] response with key 'unsupported' is ignored: "+
			"key must be a valid status code integer or equal to 'default', "+
			"'1xx', '2xx', '3xx', '4xx' or '5xx'",
		hook.LastEntry().Message,
	)
}

func TestSelectResponse_NonNumericResponse_ExpectedKeyAndStatusCode(t *testing.T) {
	tests := []struct {
		key                string
		expectedStatusCode int
	}{
		{"default", http.StatusInternalServerError},
		{"Default", http.StatusInternalServerError},
		{"1xx", http.StatusContinue},
		{"2xx", http.StatusOK},
		{"3xx", http.StatusMultipleChoices},
		{"4xx", http.StatusBadRequest},
		{"5xx", http.StatusInternalServerError},
		{"5XX", http.StatusInternalServerError},
	}
	for _, test := range tests {
		t.Run(test.key, func(t *testing.T) {
			responses := openapi3.Responses{
				test.key: &openapi3.ResponseRef{Value: openapi3.NewResponse()},
			}
			request := httptest.NewRequest("", "/", nil)

			response, code, err := mocking.SelectResponse(request, responses)

			require.NoError(t, err)
			assert.Equal(t, responses[test.key].Value, response)
			assert.Equal(t, test.expectedStatusCode, code)
		})
	}
}
