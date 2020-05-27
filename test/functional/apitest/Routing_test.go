package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"github.com/stretchr/testify/assert"
	"net/http"
	"net/http/httptest"
	"testing"
)

func (suite *APISuite) TestRouting_ValidRoute_200StatusAndExpectedContent() {
	tests := []struct {
		route           string
		expectedContent string
	}{
		{
			"/resources",
			"resourceCollection",
		},
		{
			"/resources/",
			"resourceCollection",
		},
		{
			"/resources/resourceId",
			"resourceItem",
		},
		{
			"/resources/resourceId/subresources",
			"subresourceCollection",
		},
		{
			"/resources/resourceId/subresources/subresourceId",
			"subresourceItem",
		},
		{
			"/integer-route/123",
			"integerRouteItem",
		},
	}
	for _, test := range tests {
		suite.T().Run(test.route, func(t *testing.T) {
			recorder := httptest.NewRecorder()
			handler := suite.createOpenAPIHandler(config.Configuration{
				SpecificationURL: "Routing.yaml",
			})

			request, _ := http.NewRequest("GET", test.route, nil)
			handler.ServeHTTP(recorder, request)

			assert.Equal(t, http.StatusOK, recorder.Code)
			assert.Equal(t, "application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
			assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
				json.Node("$.key").EqualToTheString(test.expectedContent)
			})
		})
	}
}

func (suite *APISuite) TestRouting_InvalidRoute_404Status() {
	tests := []struct {
		route string
	}{
		{
			"/integer-route/",
		},
		{
			"/integer-route/nonIntegerId",
		},
	}
	for _, test := range tests {
		suite.T().Run(test.route, func(t *testing.T) {
			recorder := httptest.NewRecorder()
			handler := suite.createOpenAPIHandler(config.Configuration{
				SpecificationURL: "Routing.yaml",
			})

			request, _ := http.NewRequest("GET", test.route, nil)
			handler.ServeHTTP(recorder, request)

			assert.Equal(t, http.StatusNotFound, recorder.Code)
		})
	}
}
