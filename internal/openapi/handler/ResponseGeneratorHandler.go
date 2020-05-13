package handler

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/openapi/generator"
	"swagger-mock/internal/openapi/responder"
	"swagger-mock/pkg/logcontext"
)

type responseGeneratorHandler struct {
	router            *openapi3filter.Router
	responseGenerator generator.ResponseGenerator
	responder         responder.Responder
}

func NewResponseGeneratorHandler(
	router *openapi3filter.Router,
	responseGenerator generator.ResponseGenerator,
	responder responder.Responder,
) http.Handler {
	return &responseGeneratorHandler{
		router:            router,
		responseGenerator: responseGenerator,
		responder:         responder,
	}
}

func (handler *responseGeneratorHandler) ServeHTTP(writer http.ResponseWriter, request *http.Request) {
	logger := logcontext.LoggerFromContext(request.Context())

	route, pathParameters, err := handler.router.FindRoute(request.Method, request.URL)

	if err != nil {
		http.NotFound(writer, request)

		logger.Debugf("Route '%s %s' was not found", request.Method, request.URL)
		return
	}

	routingValidation := &openapi3filter.RequestValidationInput{
		Request:    request,
		PathParams: pathParameters,
		Route:      route,
		Options: &openapi3filter.Options{
			ExcludeRequestBody: true,
		},
	}

	err = openapi3filter.ValidateRequest(request.Context(), routingValidation)
	if err != nil {
		http.NotFound(writer, request)
		logger.Infof("Route '%s %s' does not pass validation: %v", request.Method, request.URL, err.Error())

		return
	}

	response, err := handler.responseGenerator.GenerateResponse(request, route)
	if err != nil {
		handler.responder.WriteUnexpectedError(writer, err.Error())
		return
	}

	handler.responder.WriteResponse(writer, response)
}
