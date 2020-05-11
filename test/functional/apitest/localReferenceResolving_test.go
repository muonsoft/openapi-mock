package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/jsonassert"
)

func (suite *APISuite) TestLocalReferenceResolving_LocalReferences_AllReferencesResolved() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "local-reference-resolving.yaml",
	})

	request, _ := http.NewRequest("GET", "/entity/12345", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeShouldBeANumberInRange("$.id", 0, 65535)
	json.AssertNodeEqualToTheString("$.tags[0]", "tag")
}
