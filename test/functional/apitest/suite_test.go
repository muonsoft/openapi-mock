package apitest

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/stretchr/testify/suite"
	"net/http"
	"swagger-mock/internal/di/config"
	"swagger-mock/internal/di/container"
	"testing"
)

type ApiSuite struct {
	suite.Suite
}

func TestApi(t *testing.T) {
	suite.Run(t, new(ApiSuite))
}

func (suite *ApiSuite) createOpenApiHandler(specificationFilename string) http.Handler {
	router := openapi3filter.NewRouter().WithSwaggerFromFile("./../../resources/openapi-files/" + specificationFilename)
	diContainer := container.New(config.Configuration{})

	return diContainer.CreateOpenApiHandler(router)
}
