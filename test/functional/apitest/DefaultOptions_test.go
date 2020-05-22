package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/assertjson"
)

func (suite *APISuite) TestDefaultOptions_ConfigurationWithDefaultOptions_ExpectedValuesGenerated() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "DefaultOptions.yaml",
		DefaultMinInt:    100,
		DefaultMaxInt:    105,
		DefaultMinFloat:  200.0,
		DefaultMaxFloat:  205.0,
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.int").ShouldBeANumberInRange(100, 105)
		json.Node("$.float").ShouldBeANumberInRange(200.0, 205.0)
	})
}
