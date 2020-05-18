package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/assertjson"
)

func (suite *APISuite) TestCommonPathParameters_CommonPathParametersAndGETRequest_RouteResolved() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "CommonPathParameters.yaml",
	})

	request, _ := http.NewRequest("GET", "/entity/123", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.key").EqualToTheString("getEntity")
	})
}

func (suite *APISuite) TestCommonPathParameters_CommonPathParametersAndPUTRequest_RouteResolved() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "CommonPathParameters.yaml",
	})

	request, _ := http.NewRequest("PUT", "/entity/123", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.key").EqualToTheString("putEntity")
	})
}
