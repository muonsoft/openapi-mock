package server

import (
	"context"
	"errors"
	"fmt"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"
)

type httpServer struct {
	server *http.Server
}

func New(port uint16, handler http.Handler, logger *log.Logger) Server {
	return &httpServer{
		server: &http.Server{
			Addr:           fmt.Sprintf(":%d", port),
			Handler:        handler,
			ErrorLog:       logger,
			ReadTimeout:    5 * time.Second,
			WriteTimeout:   10 * time.Second,
			IdleTimeout:    30 * time.Second,
			MaxHeaderBytes: 1 << 20,
		},
	}
}

func (httpServer *httpServer) Run() error {
	hostname, err := os.Hostname()
	if err != nil {
		return err
	}

	done := make(chan bool)
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)

	go func() {
		<-quit
		httpServer.server.ErrorLog.Printf("%s - Shutdown signal received...\n", hostname)

		ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
		defer cancel()

		httpServer.server.SetKeepAlivesEnabled(false)

		if err := httpServer.server.Shutdown(ctx); err != nil {
			httpServer.server.ErrorLog.Fatalf("Could not gracefully shutdown the server: %v\n", err)
		}

		close(done)
	}()

	httpServer.server.ErrorLog.Printf("%s - Starting server on port %v", hostname, httpServer.server.Addr)

	if err := httpServer.server.ListenAndServe(); errors.Is(err, http.ErrServerClosed) {
		httpServer.server.ErrorLog.Fatalf("Could not listen on %s: %v\n", httpServer.server.Addr, err)
	}

	<-done
	httpServer.server.ErrorLog.Printf("%s - Server has been gracefully stopped.\n", hostname)

	return nil
}
