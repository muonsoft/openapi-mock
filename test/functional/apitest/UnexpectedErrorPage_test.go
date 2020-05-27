package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
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
	response := recorder.Body.String()
	suite.Contains(response, "<h1>Unexpected error</h1>")
	suite.Contains(response, "attempts limit exceeded")
	suite.Contains(response, "it seems to be a problem with the application")
}

func (suite *APISuite) TestUnexpectedErrorPage_InvalidSchemaAndErrorsAreSuppressed_200StatusAndDefaultValue() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "UnexpectedErrorPage.yaml",
		SuppressErrors:   true,
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.key[0]").EqualToTheString("value")
	})
}
