package handler

import (
	"net/http"
	"strings"

	"github.com/getkin/kin-openapi/routers"
)

type optionsHandler struct {
	router      routers.Router
	nextHandler http.Handler
}

func (handler *optionsHandler) ServeHTTP(writer http.ResponseWriter, request *http.Request) {
	if request.Method == http.MethodOptions {
		handler.respond(writer, request)
	} else {
		handler.nextHandler.ServeHTTP(writer, request)
	}
}

func (handler *optionsHandler) respond(writer http.ResponseWriter, request *http.Request) {
	var allowedMethods []string
	possibleMethods := []string{http.MethodGet, http.MethodPost, http.MethodPut, http.MethodPatch, http.MethodDelete}

	// temporary solution until new routing based on patterns
	for _, method := range possibleMethods {
		request, _ = http.NewRequest(method, request.URL.String(), request.Body)
		_, _, err := handler.router.FindRoute(request)
		if err == nil {
			allowedMethods = append(allowedMethods, method)
		}
	}

	writer.Header().Set("Allow", strings.Join(allowedMethods, ","))
	writer.WriteHeader(http.StatusOK)
}
