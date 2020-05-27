package middleware

import "net/http"

type corsHandler struct {
	nextHandler http.Handler
}

func CORSHandler(nextHandler http.Handler) http.Handler {
	return &corsHandler{nextHandler}
}

func (handler *corsHandler) ServeHTTP(writer http.ResponseWriter, request *http.Request) {
	if origin := request.Header.Get("Origin"); origin != "" {
		writer.Header().Set("Access-Control-Allow-Origin", origin)

		corsMethods := request.Header.Get("Access-Control-Request-Method")
		if corsMethods == "" {
			corsMethods = "GET,POST,PUT,DELETE"
		}
		writer.Header().Set("Access-Control-Allow-Methods", corsMethods)

		if corsHeaders := request.Header.Get("Access-Control-Request-Headers"); corsHeaders != "" {
			writer.Header().Set("Access-Control-Allow-Headers", corsHeaders)
		}
	}

	handler.nextHandler.ServeHTTP(writer, request)
}
