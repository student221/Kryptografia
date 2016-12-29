#ifndef FILECRYPTO_H
#define FILECRYPTO_H

#include <iostream>
#include <cryptopp/hex.h>

using std::string;

class FileCrypto
{
public:
    static void EncryptFile(const string & sourceFile, const string & destFile, const string & cipherMode, const string & operationMode,
                            const string & keyStoreFile, byte * iv, CryptoPP::SecByteBlock & key, const string & password);
    static void DecryptFile(const string & sourceFile, const string & destFile, const string & cipherMode, const string & operationMode,
                            const string & keyStoreFile, byte * iv, const string & password);
};

#endif // FILECRYPTO_H
