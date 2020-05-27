package apitest

import (
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
)

func (suite *APISuite) TestCORS_CORSEnabledAndRequestHasAllCORSHeaders_CORSHeadersInResponse() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "PlainTextResponse.yaml",
		CORSEnabled:      true,
	})
	request, _ := http.NewRequest("GET", "/content", nil)
	request.Header.Set("Origin", "http://example.com")
	request.Header.Set("Access-Control-Request-Method", "GET,POST")
	request.Header.Set("Access-Control-Request-Headers", "X-Custom-Header")

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("text/plain; charset=utf-8", recorder.Header().Get("Content-Type"))
	suite.Equal("http://example.com", recorder.Header().Get("Access-Control-Allow-Origin"))
	suite.Equal("GET,POST", recorder.Header().Get("Access-Control-Allow-Methods"))
	suite.Equal("X-Custom-Header", recorder.Header().Get("Access-Control-Allow-Headers"))
	suite.Equal("value", recorder.Body.String())
}

func (suite *APISuite) TestCORS_CORSDisabledAndRequestHasAllCORSHeaders_NoCORSHeadersInResponse() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "PlainTextResponse.yaml",
		CORSEnabled:      false,
	})
	request, _ := http.NewRequest("GET", "/content", nil)
	request.Header.Set("Origin", "http://example.com")
	request.Header.Set("Access-Control-Request-Method", "GET,POST")
	request.Header.Set("Access-Control-Allow-Headers", "X-Custom-Header")

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("text/plain; charset=utf-8", recorder.Header().Get("Content-Type"))
	suite.Equal("", recorder.Header().Get("Access-Control-Allow-Origin"))
	suite.Equal("", recorder.Header().Get("Access-Control-Allow-Methods"))
	suite.Equal("", recorder.Header().Get("Access-Control-Allow-Headers"))
	suite.Equal("value", recorder.Body.String())
}
