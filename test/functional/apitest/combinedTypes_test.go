package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/jsonassert"
)

func (suite *APISuite) TestCombinedTypes_SpecificationWithCombinedTypeSchemas_ExpectedValuesGenerated() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("GET", "/content", nil)
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "combined-types.yaml",
	})

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeShouldExist("$.oneOfProperty")
	json.AssertNodeShouldExist("$.oneOfProperty.id")
	json.AssertNodeEqualToTheFloat64("$.oneOfProperty.id", 0)
}
