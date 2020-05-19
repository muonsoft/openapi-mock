package negotiator

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/sirupsen/logrus"
	"github.com/sirupsen/logrus/hooks/test"
	"github.com/stretchr/testify/assert"
	"net/http"
	"swagger-mock/pkg/logcontext"
	"testing"
)

func TestStatusCodeNegotiator_NegotiateStatusCode_OnlySuccessfulResponses_ResponseWithMinCodeReturned(t *testing.T) {
	responses := openapi3.Responses{
		"204": &openapi3.ResponseRef{},
		"299": &openapi3.ResponseRef{},
		"201": &openapi3.ResponseRef{},
	}
	negotiator := NewStatusCodeNegotiator()
	request, _ := http.NewRequest("", "", nil)

	key, code, err := negotiator.NegotiateStatusCode(request, responses)

	assert.NoError(t, err)
	assert.Equal(t, "201", key)
	assert.Equal(t, http.StatusCreated, code)
}

func TestStatusCodeNegotiator_NegotiateStatusCode_OnlyErrorResponses_ResponseWithMinErrorCodeReturned(t *testing.T) {
	responses := openapi3.Responses{
		"500": &openapi3.ResponseRef{},
		"301": &openapi3.ResponseRef{},
		"400": &openapi3.ResponseRef{},
	}
	negotiator := NewStatusCodeNegotiator()
	request, _ := http.NewRequest("", "", nil)

	key, code, err := negotiator.NegotiateStatusCode(request, responses)

	assert.NoError(t, err)
	assert.Equal(t, "301", key)
	assert.Equal(t, http.StatusMovedPermanently, code)
}

func TestStatusCodeNegotiator_NegotiateStatusCode_OnlyDefaultResponse_ResponseWith500ErrorCodeReturned(t *testing.T) {
	responses := openapi3.Responses{
		"default": &openapi3.ResponseRef{},
	}
	negotiator := NewStatusCodeNegotiator()
	request, _ := http.NewRequest("", "", nil)

	key, code, err := negotiator.NegotiateStatusCode(request, responses)

	assert.NoError(t, err)
	assert.Equal(t, "default", key)
	assert.Equal(t, http.StatusInternalServerError, code)
}

func TestStatusCodeNegotiator_NegotiateStatusCode_EmptyResponses_Error(t *testing.T) {
	responses := openapi3.Responses{}
	negotiator := NewStatusCodeNegotiator()
	request, _ := http.NewRequest("", "", nil)

	key, code, err := negotiator.NegotiateStatusCode(request, responses)

	assert.True(t, errors.Is(err, ErrNoMatchingResponse))
	assert.EqualError(t, err, "[statusCodeNegotiator] failed to negotiate response: no matching response found")
	assert.Equal(t, "", key)
	assert.Equal(t, http.StatusInternalServerError, code)
}

func TestStatusCodeNegotiator_NegotiateStatusCode_InvalidResponseKey_ResponseWithMinCodeReturnedAndErrorLogged(t *testing.T) {
	responses := openapi3.Responses{
		"200":         &openapi3.ResponseRef{},
		"unsupported": &openapi3.ResponseRef{},
	}
	negotiator := NewStatusCodeNegotiator()
	request, _ := http.NewRequest("", "", nil)
	logger, hook := test.NewNullLogger()
	ctx := logcontext.WithLogger(context.Background(), logger)
	request = request.WithContext(ctx)

	key, code, err := negotiator.NegotiateStatusCode(request, responses)

	assert.NoError(t, err)
	assert.Equal(t, "200", key)
	assert.Equal(t, http.StatusOK, code)
	assert.Equal(t, logrus.WarnLevel, hook.LastEntry().Level)
	assert.Equal(
		t,
		"[statusCodeNegotiator] response with key 'unsupported' is ignored: key must be a valid status code integer or equal to 'default'",
		hook.LastEntry().Message,
	)
}
