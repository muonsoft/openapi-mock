package logcontext

import (
	"context"
	"io/ioutil"

	"github.com/sirupsen/logrus"
)

type key int

const loggerKey key = iota

func WithLogger(ctx context.Context, logger logrus.FieldLogger) context.Context {
	return context.WithValue(ctx, loggerKey, logger)
}

func LoggerFromContext(ctx context.Context) logrus.FieldLogger {
	logger, exists := ctx.Value(loggerKey).(logrus.FieldLogger)

	if !exists {
		nullLogger := logrus.New()
		nullLogger.Out = ioutil.Discard
		logger = nullLogger
	}

	return logger
}

func Infof(ctx context.Context, format string, args ...interface{}) {
	logger := LoggerFromContext(ctx)
	logger.Infof(format, args...)
}

func Warnf(ctx context.Context, format string, args ...interface{}) {
	logger := LoggerFromContext(ctx)
	logger.Warnf(format, args...)
}
