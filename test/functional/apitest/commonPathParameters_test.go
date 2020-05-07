package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/jsonassert"
)

func (suite *APISuite) TestCommonPathParameters_CommonPathParametersAndGETRequest_RouteResolved() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("GET", "/entity/123", nil)
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "common-path-parameters.yaml",
	})

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.key", "getEntity")
}

func (suite *APISuite) TestCommonPathParameters_CommonPathParametersAndPUTRequest_RouteResolved() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("PUT", "/entity/123", nil)
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "common-path-parameters.yaml",
	})

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.key", "putEntity")
}
