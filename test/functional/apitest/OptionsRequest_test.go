package apitest

import (
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
)

func (suite *APISuite) TestOptionsRequest_PathWithSomeHTTPMethods_AllowHeaderWithExpectedHTTPMethods() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "OptionsRequest.yaml",
	})
	request, _ := http.NewRequest("OPTIONS", "/content", nil)

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("", recorder.Header().Get("Content-Type"))
	suite.Equal("GET,POST", recorder.Header().Get("Allow"))
	suite.Equal("", recorder.Body.String())
}
