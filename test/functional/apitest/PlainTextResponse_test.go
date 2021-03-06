package apitest

import (
	"net/http"
	"net/http/httptest"

	"github.com/muonsoft/openapi-mock/internal/application/config"
)

func (suite *APISuite) TestPlainTextResponse_TextPlainResponseSchema_PlainTextGenerated() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "PlainTextResponse.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("text/plain; charset=utf-8", recorder.Header().Get("Content-Type"))
	suite.Equal("value", recorder.Body.String())
}
