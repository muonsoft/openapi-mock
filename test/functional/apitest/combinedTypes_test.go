package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/jsonassert"
)

func (suite *APISuite) TestCombinedTypes_SpecificationWithCombinedTypeSchemas_ExpectedValuesGenerated() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "combined-types.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeShouldExist("$.oneOfProperty")
	json.AssertNodeEqualToTheInteger("$.oneOfProperty.id", 0)
	json.AssertNodeShouldExist("$.allOfProperty")
	json.AssertNodeEqualToTheInteger("$.allOfProperty.id", 0)
	json.AssertNodeEqualToTheString("$.allOfProperty.name", "name")
	json.AssertNodeEqualToTheString("$.allOfProperty.title", "title")
	json.AssertNodeShouldExist("$.anyOfProperty")
	json.AssertNodeEqualToTheInteger("$.anyOfProperty.id", 0)
	json.AssertNodeShouldExist("$.deepAllOfProperty")
	json.AssertNodeEqualToTheInteger("$.deepAllOfProperty.id", 0)
	json.AssertNodeEqualToTheString("$.deepAllOfProperty.name", "name")
	json.AssertNodeEqualToTheString("$.deepAllOfProperty.title", "title")
	json.AssertNodeEqualToTheString("$.deepAllOfProperty.description", "description")
	json.AssertNodeEqualToTheString("$.deepAllOfProperty.type", "type")
}
