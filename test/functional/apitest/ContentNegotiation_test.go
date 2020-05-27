package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/api-testing/assertxml"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"github.com/stretchr/testify/assert"
	"net/http"
	"net/http/httptest"
	"testing"
)

func (suite *APISuite) TestContentNegotiation_OperationWithMultipleContentTypesAndJSONAcceptHeader_ExpectedContent() {
	tests := []struct {
		acceptHeader        string
		expectedFormat      string
		expectedContentType string
		expectedContent     string
	}{
		{
			"application/json",
			"json",
			"application/json; charset=utf-8",
			"json",
		},
		{
			"application/json; application/xml; text/html",
			"json",
			"application/json; charset=utf-8",
			"json",
		},
		{
			"application/ld+json; text/html",
			"json",
			"application/ld+json; charset=utf-8",
			"json ld",
		},
		{
			"application/xml",
			"xml",
			"application/xml; charset=utf-8",
			"xml",
		},
		{
			"application/soap+xml; application/xml",
			"xml",
			"application/soap+xml; charset=utf-8",
			"soap xml",
		},
	}
	for _, test := range tests {
		suite.T().Run(test.acceptHeader, func(t *testing.T) {
			recorder := httptest.NewRecorder()
			handler := suite.createOpenAPIHandler(config.Configuration{
				SpecificationURL: "ContentNegotiation.yaml",
			})

			request, _ := http.NewRequest("GET", "/content", nil)
			request.Header.Set("Accept", test.acceptHeader)
			handler.ServeHTTP(recorder, request)

			assert.Equal(t, http.StatusOK, recorder.Code)
			assert.Equal(t, test.expectedContentType, recorder.Header().Get("Content-Type"))
			if test.expectedFormat == "json" {
				assertjson.Has(t, recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
					json.Node("$.key").EqualToTheString(test.expectedContent)
				})
			} else {
				assertxml.Has(t, recorder.Body.Bytes(), func(xml *assertxml.AssertXML) {
					xml.Node("/key").EqualToTheString(test.expectedContent)
				})
			}
		})
	}
}
