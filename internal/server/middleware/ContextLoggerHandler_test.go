package middleware

import (
	"context"
	httpmock "github.com/muonsoft/openapi-mock/test/mocks/http"
	"github.com/sirupsen/logrus/hooks/test"
	"github.com/stretchr/testify/mock"
	"net/http"
	"net/http/httptest"
	"testing"
)

func TestContextualLoggerHandler_ServeHTTP_RequestIdInContext_LoggerSetToContext(t *testing.T) {
	request, _ := http.NewRequest("GET", "/", nil)
	ctx := context.WithValue(context.Background(), requestIDKey, "00000000-0000-0000-0000-000000000000")
	request = request.WithContext(ctx)
	recorder := httptest.NewRecorder()
	logger, _ := test.NewNullLogger()
	nextHandler := httpmock.Handler{}
	handler := ContextLoggerHandler(logger, &nextHandler)
	nextHandler.
		On("ServeHTTP", mock.Anything, mock.Anything).
		Return(nil).
		Once()

	handler.ServeHTTP(recorder, request)

	nextHandler.AssertExpectations(t)
}
