package responder

import (
	"errors"
	"github.com/stretchr/testify/assert"
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/openapi/generator"
	serializermock "swagger-mock/test/mocks/openapi/responder/serializer"
	"testing"
)

func TestWriteResponse_GivenResponse_SerializedDataWritten(t *testing.T) {
	tests := []struct {
		name                        string
		contentType                 string
		expectedSerializationFormat string
	}{
		{
			"json response",
			"application/json",
			"json",
		},
		{
			"json ld response",
			"application/ld+json",
			"json",
		},
		{
			"xml response",
			"application/xml",
			"xml",
		},
		{
			"xml response",
			"application/xml",
			"xml",
		},
		{
			"soap xml response",
			"application/soap+xml",
			"xml",
		},
		{
			"text html response",
			"text/html",
			"raw",
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			response := &generator.Response{
				StatusCode:  http.StatusOK,
				ContentType: test.contentType,
				Data:        "data",
			}
			serializer := &serializermock.Serializer{}
			serializer.
				On("Serialize", response.Data, test.expectedSerializationFormat).
				Return([]byte("serialized"), nil).
				Once()
			recorder := httptest.NewRecorder()
			responder := New().(*coordinatingResponder)
			responder.serializer = serializer

			responder.WriteResponse(recorder, response)

			serializer.AssertExpectations(t)
			assert.Equal(t, response.ContentType+"; charset=utf-8", recorder.Header().Get("Content-Type"))
			assert.Equal(t, "nosniff", recorder.Header().Get("X-Content-Type-Options"))
			assert.Equal(t, response.StatusCode, recorder.Code)
			assert.Equal(t, "serialized", recorder.Body.String())
		})
	}
}

func TestCoordinatingResponder_WriteResponse_SerializationError_UnexpectedErrorWritten(t *testing.T) {
	response := &generator.Response{
		StatusCode:  http.StatusOK,
		ContentType: "content/type",
		Data:        "data",
	}
	serializer := &serializermock.Serializer{}
	serializer.
		On("Serialize", response.Data, "raw").
		Return(nil, errors.New("error")).
		Once()
	recorder := httptest.NewRecorder()
	responder := New().(*coordinatingResponder)
	responder.serializer = serializer

	responder.WriteResponse(recorder, response)

	serializer.AssertExpectations(t)
	assert.Equal(t, "text/html; charset=utf-8", recorder.Header().Get("Content-Type"))
	assert.Equal(t, "nosniff", recorder.Header().Get("X-Content-Type-Options"))
	assert.Equal(t, http.StatusInternalServerError, recorder.Code)
	assert.Contains(t, recorder.Body.String(), "Unexpected error")
	assert.Contains(t, recorder.Body.String(), "An unexpected error occurred: error")
}
