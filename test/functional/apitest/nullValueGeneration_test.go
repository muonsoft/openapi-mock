package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/jsonassert"
)

func (suite *APISuite) TestNullValueGeneration_NullableTypeAndMaxProbability_NullGenerated() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "null-value-generation.yaml",
		NullProbability:  1.0,
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeIsNull("$.key")
}
