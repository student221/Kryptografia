TEMPLATE = app
CONFIG += console c++11
CONFIG -= app_bundle
CONFIG -= qt

INCLUDEPATH += "/home/jacob/Pobrane/irrKlang-64bit-1.5.0/include"

LIBS += -L"/usr/local/lib" -lcryptopp
LIBS += -L"/usr/lib" /home/jacob/Pobrane/irrKlang-64bit-1.5.0/bin/linux-gcc-64/libIrrKlang.so -pthread

SOURCES += main.cpp \
    filecrypto.cpp

HEADERS += \
    filecrypto.h
