package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/pkg/jsonassert"
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
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.key", "generatedValue")
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
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.key", "exampleValue")
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
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.key", "multiExampleValue")
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
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.stringExample", "stringValue")
	json.AssertNodeEqualToTheFloat64("$.numberExample", 123)
	json.AssertNodeIsTrue("$.booleanExample")
	json.AssertNodeEqualToTheString("$.objectExample.key", "objectValue")
	json.AssertNodeEqualToTheString("$.arrayExample[0]", "arrayValue")
}
