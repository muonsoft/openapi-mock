package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
)

func (suite *APISuite) TestLocalCrossReference_LocalPathReference_ReferenceResolved() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "LocalCrossReference.yaml",
	})

	request, _ := http.NewRequest("GET", "/entities", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$[0].key").EqualToTheString("value")
	})
}
