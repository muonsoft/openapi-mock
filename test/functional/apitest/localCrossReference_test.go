package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/jsonassert"
)

func (suite *APISuite) TestLocalCrossReference_LocalPathReference_ReferenceResolved() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "local-cross-reference.yaml",
	})

	request, _ := http.NewRequest("GET", "/entities", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeEqualToTheString("$[0].key", "value")
}
