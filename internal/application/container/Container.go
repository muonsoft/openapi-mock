package container

import (
	"net/http"

	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/muonsoft/openapi-mock/internal/openapi/loader"
	"github.com/muonsoft/openapi-mock/internal/server"
	"github.com/sirupsen/logrus"
)

type Container interface {
	GetLogger() logrus.FieldLogger
	CreateSpecificationLoader() loader.SpecificationLoader
	CreateHTTPHandler(router *openapi3filter.Router) http.Handler
	CreateHTTPServer() server.Server
}
