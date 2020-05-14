package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
)

func (suite *APISuite) TestRespondingNoContent_NoContentResponse_204StatusAndEmptyResponseBody() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "responding-no-content.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusNoContent, recorder.Code)
	suite.Equal("", recorder.Header().Get("Content-Type"))
	suite.Equal("", recorder.Body.String())
}
