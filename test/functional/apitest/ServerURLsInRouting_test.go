package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"github.com/stretchr/testify/assert"
	"net/http"
	"net/http/httptest"
	"testing"
)

func (suite *APISuite) TestServerURLsInRouting_SendingGETToEndpointsWithGlobalAndPathAndEndpointServers_200StatusAndExpectedContent() {
	tests := []struct {
		route string
	}{
		{"/global/base/path/endpoint"},
		{"/another-global/base/path/endpoint"},
		// Server override at path and endpoint level is not supported yet because it is not
		// supported in https://github.com/getkin/kin-openapi
		// {"/local/base/path/second-endpoint"},
		// {"/another-local/base/path/second-endpoint"},
		// {"/endpoint/base/path/third-endpoint"},
		// {"/another-endpoint/base/path/third-endpoint"},
	}
	for _, test := range tests {
		suite.T().Run(test.route, func(t *testing.T) {
			recorder := httptest.NewRecorder()
			handler := suite.createOpenAPIHandler(config.Configuration{
				SpecificationURL: "ServerURLsInRouting.yaml",
			})

			request, _ := http.NewRequest("GET", test.route, nil)
			handler.ServeHTTP(recorder, request)

			assert.Equal(t, http.StatusOK, recorder.Code)
			assert.Equal(t, "application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
			assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
				json.Node("$.key").EqualToTheString("value")
			})
		})
	}
}

func (suite *APISuite) TestServerURLsInRouting_SendingPOSTToNonExistentNotOverriddenEndpoints_404Status() {
	tests := []struct {
		route string
	}{
		{"/endpoint/base/path/third-endpoint"},
		{"/another-endpoint/base/path/third-endpoint"},
	}
	for _, test := range tests {
		suite.T().Run(test.route, func(t *testing.T) {
			recorder := httptest.NewRecorder()
			handler := suite.createOpenAPIHandler(config.Configuration{
				SpecificationURL: "ServerURLsInRouting.yaml",
			})

			request, _ := http.NewRequest("POST", test.route, nil)
			handler.ServeHTTP(recorder, request)

			assert.Equal(t, http.StatusNotFound, recorder.Code)
		})
	}
}

func (suite *APISuite) TestServerURLsInRouting_SendingPOSTToExistentNotOverriddenEndpoints_200Status() {
	tests := []struct {
		route string
	}{
		{"/global/base/path/third-endpoint"},
		{"/another-global/base/path/third-endpoint"},
	}
	for _, test := range tests {
		suite.T().Run(test.route, func(t *testing.T) {
			recorder := httptest.NewRecorder()
			handler := suite.createOpenAPIHandler(config.Configuration{
				SpecificationURL: "ServerURLsInRouting.yaml",
			})

			request, _ := http.NewRequest("POST", test.route, nil)
			handler.ServeHTTP(recorder, request)

			assert.Equal(t, http.StatusOK, recorder.Code)
			assert.Equal(t, "application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
			assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
				json.Node("$.key").EqualToTheString("value")
			})
		})
	}
}
