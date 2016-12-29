#ifndef FILECRYPTO_H
#define FILECRYPTO_H

#include <iostream>
#include <cryptopp/hex.h>

using std::string;

class FileCrypto
{
public:
    static void EncryptFile(const string & sourceFile, const string & destFile, const string & keyStoreFile, const string & confFile,
                            CryptoPP::SecByteBlock & iv, CryptoPP::SecByteBlock & key, const string & password);
    static void DecryptFile(const string & sourceFile, const string & destFile, const string & confFile,
                            const string & password);
private:
    static void split(const string &s, char delim, std::vector<string> &elems);
    static std::vector<string> split(const string &s, char delim);
};

#endif // FILECRYPTO_H
