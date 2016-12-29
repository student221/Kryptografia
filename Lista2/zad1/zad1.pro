TEMPLATE = app
QT += core
QT -= gui

CONFIG += console c++11
CONFIG -= app_bundle
CONFIG += c++11
LIBS += -L"/usr/local/lib" -lcryptopp
LIBS += -lpthread
SOURCES += main.cpp
