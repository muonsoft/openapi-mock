package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
)

func (suite *APISuite) TestUnexpectedErrorPage_InvalidSchema_500StatusAndErrorPage() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "UnexpectedErrorPage.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusInternalServerError, recorder.Code)
	suite.Equal("text/html; charset=utf-8", recorder.Header().Get("Content-Type"))
	suite.Contains(recorder.Body.String(), "<h1>Unexpected error</h1>")
}
