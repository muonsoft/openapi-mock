package responder

import (
	"context"
	"errors"
	apperrors "github.com/muonsoft/openapi-mock/internal/errors"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator"
	serializermock "github.com/muonsoft/openapi-mock/test/mocks/openapi/responder/serializer"
	"github.com/stretchr/testify/assert"
	"net/http"
	"net/http/httptest"
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

			responder.WriteResponse(context.Background(), recorder, response)

			serializer.AssertExpectations(t)
			assert.Equal(t, response.ContentType+"; charset=utf-8", recorder.Header().Get("Content-Type"))
			assert.Equal(t, response.StatusCode, recorder.Code)
			assert.Equal(t, "serialized", recorder.Body.String())
		})
	}
}

func TestCoordinatingResponder_WriteResponse_NoContentResponse_EmptyBodyWritten(t *testing.T) {
	response := &generator.Response{
		StatusCode:  http.StatusNoContent,
		ContentType: "",
		Data:        "",
	}
	serializer := &serializermock.Serializer{}
	serializer.On("Serialize", response.Data, "raw").Return([]byte(""), nil).Once()
	recorder := httptest.NewRecorder()
	responder := New().(*coordinatingResponder)
	responder.serializer = serializer

	responder.WriteResponse(context.Background(), recorder, response)

	serializer.AssertExpectations(t)
	assert.Equal(t, "", recorder.Header().Get("Content-Type"))
	assert.Equal(t, http.StatusNoContent, recorder.Code)
	assert.Equal(t, "", recorder.Body.String())
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

	responder.WriteResponse(context.Background(), recorder, response)

	serializer.AssertExpectations(t)
	assert.Equal(t, "text/html; charset=utf-8", recorder.Header().Get("Content-Type"))
	assert.Equal(t, http.StatusInternalServerError, recorder.Code)
	assert.Contains(t, recorder.Body.String(), "<h1>Unexpected error</h1>")
	assert.Contains(t, recorder.Body.String(), "An unexpected error occurred:<br>error")
}

func TestCoordinatingResponder_WriteError_UnsupportedFeatureError_UnsupportedPage(t *testing.T) {
	recorder := httptest.NewRecorder()
	responder := New()
	notSupported := &apperrors.NotSupported{Message: "unsupported feature description"}

	responder.WriteError(context.Background(), recorder, notSupported)
	response := recorder.Body.String()

	assert.Equal(t, "text/html; charset=utf-8", recorder.Header().Get("Content-Type"))
	assert.Equal(t, http.StatusInternalServerError, recorder.Code)
	assert.Contains(t, response, "<h1>Feature is not supported</h1>")
	assert.Contains(t, response, "An error occurred: unsupported feature description.")
	assert.Contains(t, response, "If you want this feature to be supported, please make an issue at the project page")
}
