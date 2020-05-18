package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/pkg/assertjson"
)

func (suite *APISuite) TestExampleUsage_SingleExampleInMediaAndUseExamplesDisabled_GeneratedValueInResponse() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "ExampleUsage/MediaTypeExample.yaml",
		UseExamples:      generator.No,
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.key").EqualToTheString("generatedValue")
	})
}

func (suite *APISuite) TestExampleUsage_SingleExampleInMediaAndUseExamplesIfPresent_ExampleInResponse() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "ExampleUsage/MediaTypeExample.yaml",
		UseExamples:      generator.IfPresent,
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.key").EqualToTheString("exampleValue")
	})
}

func (suite *APISuite) TestExampleUsage_MultipleExamplesInMediaAndUseExamplesIfPresent_ExampleInResponse() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "ExampleUsage/MediaTypeExamples.yaml",
		UseExamples:      generator.IfPresent,
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.key").EqualToTheString("multiExampleValue")
	})
}

func (suite *APISuite) TestExampleUsage_ValueExamplesAndUseExamplesIfPresent_ExamplesInResponse() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "ExampleUsage/ValueExamples.yaml",
		UseExamples:      generator.IfPresent,
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.stringExample").EqualToTheString("stringValue")
		json.Node("$.numberExample").EqualToTheFloat64(123)
		json.Node("$.booleanExample").IsTrue()
		json.Node("$.objectExample.key").EqualToTheString("objectValue")
		json.Node("$.arrayExample[0]").EqualToTheString("arrayValue")
	})
}
