package apitest

import (
	"net/http"
	"net/http/httptest"

	"github.com/muonsoft/openapi-mock/internal/application/config"
)

func (suite *APISuite) TestHTMLResponse_TextHTMLResponseSchema_HTMLGenerated() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "HTMLResponse.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("text/html; charset=utf-8", recorder.Header().Get("Content-Type"))
	html := recorder.Body.String()
	suite.Contains(html, `<html lang="en">`)
	suite.Contains(html, "<body>")
	suite.Contains(html, "<h1>")
}
