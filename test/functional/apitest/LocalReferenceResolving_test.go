package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
)

func (suite *APISuite) TestLocalReferenceResolving_LocalReferences_AllReferencesResolved() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "LocalReferenceResolving.yaml",
	})

	request, _ := http.NewRequest("GET", "/entity/12345", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.id").IsNumberInRange(0, 65535)
		json.Node("$.tags[0]").EqualToTheString("tag")
	})
}
