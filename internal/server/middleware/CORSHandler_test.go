package middleware

import (
	httpmock "github.com/muonsoft/openapi-mock/test/mocks/http"
	"github.com/stretchr/testify/assert"
	"net/http"
	"net/http/httptest"
	"testing"
)

func TestCORSHandler_ServeHTTP_RequestWithoutOrigin_RequestPassedToNextHandler(t *testing.T) {
	request, _ := http.NewRequest("GET", "/", nil)
	recorder := httptest.NewRecorder()
	nextHandler := &httpmock.Handler{}
	nextHandler.On("ServeHTTP", recorder, request).Return().Once()
	handler := CORSHandler(nextHandler)

	handler.ServeHTTP(recorder, request)

	nextHandler.AssertExpectations(t)
	assert.Equal(t, "", recorder.Header().Get("Access-Control-Allow-Origin"))
	assert.Equal(t, "", recorder.Header().Get("Access-Control-Allow-Methods"))
	assert.Equal(t, "", recorder.Header().Get("Access-Control-Allow-Headers"))
}

func TestCORSHandler_ServeHTTP_RequestWithAllCORSHeaders_CORSHeadersAddedToResponseAndRequestPassedToNextHandler(t *testing.T) {
	request, _ := http.NewRequest("GET", "/", nil)
	request.Header.Set("Origin", "http://example.com")
	request.Header.Set("Access-Control-Request-Method", "GET,POST")
	request.Header.Set("Access-Control-Request-Headers", "X-Custom-Header")
	recorder := httptest.NewRecorder()
	nextHandler := &httpmock.Handler{}
	nextHandler.On("ServeHTTP", recorder, request).Return().Once()
	handler := CORSHandler(nextHandler)

	handler.ServeHTTP(recorder, request)

	nextHandler.AssertExpectations(t)
	assert.Equal(t, "http://example.com", recorder.Header().Get("Access-Control-Allow-Origin"))
	assert.Equal(t, "GET,POST", recorder.Header().Get("Access-Control-Allow-Methods"))
	assert.Equal(t, "X-Custom-Header", recorder.Header().Get("Access-Control-Allow-Headers"))
}

func TestCORSHandler_ServeHTTP_RequestWithOnlyOrigin_DefaultCORSHeadersAddedToResponseAndRequestPassedToNextHandler(t *testing.T) {
	request, _ := http.NewRequest("GET", "/", nil)
	request.Header.Set("Origin", "http://example.com")
	recorder := httptest.NewRecorder()
	nextHandler := &httpmock.Handler{}
	nextHandler.On("ServeHTTP", recorder, request).Return().Once()
	handler := CORSHandler(nextHandler)

	handler.ServeHTTP(recorder, request)

	nextHandler.AssertExpectations(t)
	assert.Equal(t, "http://example.com", recorder.Header().Get("Access-Control-Allow-Origin"))
	assert.Equal(t, "GET,POST,PUT,DELETE", recorder.Header().Get("Access-Control-Allow-Methods"))
	assert.Equal(t, "", recorder.Header().Get("Access-Control-Allow-Headers"))
}
