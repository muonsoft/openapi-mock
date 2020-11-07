package apitest

import (
	"net/http"
	"net/http/httptest"

	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
)

func (suite *APISuite) TestDefaultResponse_OnlyDefaultResponse_500StatusAndDefaultContent() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "DefaultResponse.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusInternalServerError, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("/key").EqualToTheString("value")
	})
}
