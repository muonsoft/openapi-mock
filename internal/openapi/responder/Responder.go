package responder

import (
	"net/http"
	"swagger-mock/internal/openapi/generator"
)

type Responder interface {
	WriteResponse(writer http.ResponseWriter, response *generator.Response)
}

func New() Responder {
	return &coordinatingResponder{}
}
