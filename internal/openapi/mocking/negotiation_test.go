package mocking_test

import (
	"net/http/httptest"
	"testing"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/openapi/mocking"
	"github.com/stretchr/testify/assert"
)

func TestNegotiateContentType_GivenContentTypesAndRequest_ExpectedContentType(t *testing.T) {
	tests := []struct {
		name                string
		acceptHeader        string
		content             map[string]*openapi3.MediaType
		expectedContentType string
	}{
		{
			"json accept header, json + xml contents",
			"application/json",
			map[string]*openapi3.MediaType{
				"application/json": openapi3.NewMediaType(),
				"application/xml":  openapi3.NewMediaType(),
			},
			"application/json",
		},
		{
			"xml accept header, json + xml contents",
			"application/xml",
			map[string]*openapi3.MediaType{
				"application/json": openapi3.NewMediaType(),
				"application/xml":  openapi3.NewMediaType(),
			},
			"application/xml",
		},
		{
			"xml accept header, json + xml contents",
			"application/xml",
			map[string]*openapi3.MediaType{
				"application/json": openapi3.NewMediaType(),
				"application/xml":  openapi3.NewMediaType(),
			},
			"application/xml",
		},
		{
			"xml + json accept header, text + json content",
			"application/xml, application/json",
			map[string]*openapi3.MediaType{
				"text/html":        openapi3.NewMediaType(),
				"application/json": openapi3.NewMediaType(),
			},
			"application/json",
		},
		{
			"empty accept header, json content",
			"",
			map[string]*openapi3.MediaType{
				"application/json": openapi3.NewMediaType(),
			},
			"application/json",
		},
		{
			"empty accept header, empty content",
			"",
			map[string]*openapi3.MediaType{},
			"",
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			request := httptest.NewRequest("", "/", nil)
			request.Header.Set("Accept", test.acceptHeader)
			response := openapi3.NewResponse()
			response.Content = test.content

			contentType := mocking.NegotiateContentType(request, response)

			assert.Equal(t, test.expectedContentType, contentType)
		})
	}
}
