package router

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/url"
)

type Router interface {
	FindRoute(method string, url *url.URL) (*openapi3filter.Route, map[string]string, error)
}
