package handler

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"strings"
)

type optionsHandler struct {
	router      *openapi3filter.Router
	nextHandler http.Handler
}

func (handler *optionsHandler) ServeHTTP(writer http.ResponseWriter, request *http.Request) {
	if request.Method == "OPTIONS" {
		handler.respond(writer, request)
	} else {
		handler.nextHandler.ServeHTTP(writer, request)
	}
}

func (handler *optionsHandler) respond(writer http.ResponseWriter, request *http.Request) {
	var allowedMethods []string
	possibleMethods := []string{"GET", "POST", "PUT", "PATCH", "DELETE"}

	// temporary solution until new routing based on patterns
	for _, method := range possibleMethods {
		_, _, err := handler.router.FindRoute(method, request.URL)
		if err == nil {
			allowedMethods = append(allowedMethods, method)
		}
	}

	writer.Header().Set("Allow", strings.Join(allowedMethods, ","))
	writer.WriteHeader(http.StatusOK)
}
