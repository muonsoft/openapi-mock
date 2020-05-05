package responder

import (
	"net/http"
	"swagger-mock/internal/infrastructure/openapi/generator"
)

type Responder interface {
	WriteResponse(writer http.ResponseWriter, response *generator.Response)
}

func New() Responder {
	return &coordinatingResponder{}
}
