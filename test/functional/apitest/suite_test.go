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

func (suite *ApiSuite) createOpenApiHandler(configuration config.Configuration) http.Handler {
	router := openapi3filter.NewRouter().WithSwaggerFromFile("./../../resources/openapi-files/" + configuration.SpecificationUrl)
	diContainer := container.New(configuration)

	return diContainer.CreateOpenApiHandler(router)
}
