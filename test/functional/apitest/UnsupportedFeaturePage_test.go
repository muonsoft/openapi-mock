package apitest

import (
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
)

func (suite *APISuite) TestUnsupportedFeaturePage_InvalidSchema_500StatusAndErrorPage() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "UnsupportedFeaturePage.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)
	response := recorder.Body.String()

	suite.Equal(http.StatusInternalServerError, recorder.Code)
	suite.Equal("text/html; charset=utf-8", recorder.Header().Get("Content-Type"))
	suite.Contains(response, "<h1>Feature is not supported</h1>")
	suite.Contains(response, "generating response for content type 'unsupported/content' is not supported")
	suite.Contains(response, "If you want this feature to be supported, please make an issue at the project page")
}
