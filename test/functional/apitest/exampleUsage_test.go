package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/pkg/jsonassert"
)

func (suite *ApiSuite) TestExampleUsage_SingleExampleInMediaAndUseExamplesIfPresent_ExampleInResponse() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("GET", "/content", nil)
	handler := suite.createOpenApiHandler(config.Configuration{
		SpecificationUrl: "example-usage/media-type-example.yaml",
		UseExamples:      generator.IfPresent,
	})

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.key", "exampleValue")
}

func (suite *ApiSuite) TestExampleUsage_MultipleExamplesInMediaAndUseExamplesIfPresent_ExampleInResponse() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("GET", "/content", nil)
	handler := suite.createOpenApiHandler(config.Configuration{
		SpecificationUrl: "example-usage/media-type-examples.yaml",
		UseExamples:      generator.IfPresent,
	})

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.key", "multiExampleValue")
}

func (suite *ApiSuite) TestExampleUsage_ValueExamplesAndUseExamplesIfPresent_ExamplesInResponse() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("GET", "/content", nil)
	handler := suite.createOpenApiHandler(config.Configuration{
		SpecificationUrl: "example-usage/value-examples.yaml",
		UseExamples:      generator.IfPresent,
	})

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.stringExample", "stringValue")
	json.AssertNodeEqualToTheFloat64("$.numberExample", 123)
	json.AssertNodeIsTrue("$.booleanExample")
	json.AssertNodeEqualToTheString("$.objectExample.key", "objectValue")
	json.AssertNodeEqualToTheString("$.arrayExample[0]", "arrayValue")
}
