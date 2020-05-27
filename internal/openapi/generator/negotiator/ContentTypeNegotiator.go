package negotiator

import (
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/go-ozzo/ozzo-routing/v2/content"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	"net/http"
)

type ContentTypeNegotiator interface {
	NegotiateContentType(request *http.Request, response *openapi3.Response) string
}

func NewContentTypeNegotiator() ContentTypeNegotiator {
	return &contentTypeNegotiator{}
}

type contentTypeNegotiator struct{}

func (negotiator *contentTypeNegotiator) NegotiateContentType(request *http.Request, response *openapi3.Response) string {
	logger := logcontext.LoggerFromContext(request.Context())

	if len(response.Content) == 0 {
		logger.Infof("[contentTypeNegotiator] response has no content")

		return ""
	}

	contentTypes := negotiator.getContentTypes(response)
	contentType := content.NegotiateContentType(request, contentTypes, contentTypes[0])

	logger.Infof(
		"[contentTypeNegotiator] best media type '%s' was negotiated for accept header '%s'",
		contentType,
		request.Header.Get("Accept"),
	)

	return contentType
}

func (negotiator *contentTypeNegotiator) getContentTypes(response *openapi3.Response) []string {
	contentTypes := make([]string, 0, len(response.Content))

	for contentType := range response.Content {
		contentTypes = append(contentTypes, contentType)
	}

	return contentTypes
}
