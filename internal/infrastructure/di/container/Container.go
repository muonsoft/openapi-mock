package container

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/sirupsen/logrus"
	"net/http"
	"swagger-mock/internal/infrastructure/openapi/loader"
)

type Container interface {
	GetLogger() logrus.FieldLogger
	CreateSpecificationLoader() loader.SpecificationLoader
	CreateOpenApiHandler(router *openapi3filter.Router) http.Handler
}
