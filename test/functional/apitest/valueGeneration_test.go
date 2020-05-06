package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/pkg/jsonassert"
)

func (suite *ApiSuite) TestGETContent_NoParameters_200GeneratedValues() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("GET", "/content", nil)
	handler := suite.createOpenApiHandler("value-generation.yaml")

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$.stringValue", "value")
}
