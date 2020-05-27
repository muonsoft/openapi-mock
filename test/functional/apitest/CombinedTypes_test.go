package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
)

func (suite *APISuite) TestCombinedTypes_SpecificationWithCombinedTypeSchemas_ExpectedValuesGenerated() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "CombinedTypes.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.oneOfProperty").Exists()
		json.Node("$.oneOfProperty.id").EqualToTheInteger(0)
		json.Node("$.allOfProperty").Exists()
		json.Node("$.allOfProperty.id").EqualToTheInteger(0)
		json.Node("$.allOfProperty.name").EqualToTheString("name")
		json.Node("$.allOfProperty.title").EqualToTheString("title")
		json.Node("$.anyOfProperty").Exists()
		json.Node("$.anyOfProperty.id").EqualToTheInteger(0)
		json.Node("$.deepAllOfProperty").Exists()
		json.Node("$.deepAllOfProperty.id").EqualToTheInteger(0)
		json.Node("$.deepAllOfProperty.name").EqualToTheString("name")
		json.Node("$.deepAllOfProperty.title").EqualToTheString("title")
		json.Node("$.deepAllOfProperty.description").EqualToTheString("description")
		json.Node("$.deepAllOfProperty.type").EqualToTheString("type")
	})
}
