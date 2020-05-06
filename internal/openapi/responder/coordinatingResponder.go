package responder

import (
	"encoding/json"
	"net/http"
	"swagger-mock/internal/openapi/generator"
)

type coordinatingResponder struct{}

func (responder *coordinatingResponder) WriteResponse(writer http.ResponseWriter, response *generator.Response) {
	data, err := json.Marshal(response.Data)
	if err != nil {
		http.Error(writer, err.Error(), http.StatusInternalServerError)
		return
	}

	writer.Header().Set("Content-Type", response.MediaType)
	writer.WriteHeader(response.StatusCode)
	_, _ = writer.Write(data)
}
