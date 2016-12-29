#include "filecrypto.h"

#include <iostream>
#include <cryptopp/files.h>
#include <cryptopp/default.h>
#include <cryptopp/gcm.h>
#include <cryptopp/eax.h>
#include <cryptopp/aes.h>

using namespace CryptoPP;
using namespace std;

void FileCrypto::EncryptFile(const string &sourceFile, const string &destFile, const string &cipherMode, const string &operationMode,
                 const string &keyStoreFile, byte *iv, SecByteBlock &key, const string &password)
{
    string key_string;
    HexEncoder hex(new StringSink(key_string));
    hex.Put(key, key.size());
    hex.MessageEnd();

    //save key to keystore
    StringSource ss(key_string, true,
           new DefaultEncryptorWithMAC(
               (byte*)password.data(), password.size(),
                  new FileSink(keyStoreFile.c_str())));

    if (cipherMode == "AES")
    {
        if (operationMode == "CBC")
        {
            CBC_Mode<AES>::Encryption encryption;
            encryption.SetKeyWithIV(key, key.size(), iv);

            //encrypt file
            FileSource fs(sourceFile.c_str(), true,
                      new StreamTransformationFilter(encryption,
                        new HexEncoder(
                            new FileSink(destFile.c_str()))));
        }
        else if (operationMode == "CTR")
        {
            CTR_Mode<AES>::Encryption encryption;
            encryption.SetKeyWithIV(key, key.size(), iv);

            //encrypt file
            FileSource fs(sourceFile.c_str(), true,
                      new StreamTransformationFilter(encryption,
                        new HexEncoder(
                            new FileSink(destFile.c_str()))));
        }
        else if (operationMode == "EAX")
        {
            EAX<AES>::Encryption encryption;
            encryption.SetKeyWithIV(key, key.size(), iv, sizeof(iv));

            //encrypt file
            FileSource fs(sourceFile.c_str(), true,
                      new AuthenticatedEncryptionFilter(encryption,
                        new HexEncoder(
                            new FileSink(destFile.c_str()))));
        }
        else if (operationMode == "GCM")
        {
            GCM<AES>::Encryption encryption;
            encryption.SetKeyWithIV(key, key.size(), iv, sizeof(iv));

            FileSource fs(sourceFile.c_str(), true,
                      new AuthenticatedEncryptionFilter(encryption,
                        new HexEncoder(
                            new FileSink(destFile.c_str()))));
        }
    }

    cout << "\nFile encrypted successfully\n";
}

void FileCrypto::DecryptFile(const string &sourceFile, const string &destFile, const string &cipherMode, const string &operationMode,
                             const string &keyStoreFile, byte *iv, const string &password)
{
    //load key
    try
    {
        string key_string = "";
        string keyFromFile;
        FileSource fsd(keyStoreFile.c_str(), true,
                       new DefaultDecryptorWithMAC(
                           (byte*)password.data(), password.size(),
                              new StringSink(keyFromFile)));

        HexDecoder hexDecoder(new StringSink(key_string));
        hexDecoder.Put((byte*)keyFromFile.c_str(), keyFromFile.size());
        hexDecoder.MessageEnd();

        SecByteBlock key((byte*)key_string.c_str(), 16);

        if (cipherMode == "AES")
        {
            if (operationMode == "CBC")
            {
                //decrypt
                CBC_Mode<AES>::Decryption decryption;
                decryption.SetKeyWithIV(key, key.size(), iv);

                FileSource decryptord(sourceFile.c_str(), true,
                            new HexDecoder(
                                new StreamTransformationFilter(decryption,
                                    new FileSink(destFile.c_str()))
                            )
                        );
            }
            else if (operationMode == "CTR")
            {
                CTR_Mode<AES>::Decryption decryption;
                decryption.SetKeyWithIV(key, key.size(), iv);

                //encrypt file
                FileSource fs(sourceFile.c_str(), true,
                        new HexDecoder(
                          new StreamTransformationFilter(decryption,
                                new FileSink(destFile.c_str()))));
            }
            else if (operationMode == "EAX")
            {
                EAX<AES>::Decryption decryption;
                decryption.SetKeyWithIV(key, key.size(), iv, sizeof(iv));

                //encrypt file
                FileSource fs(sourceFile.c_str(), true,
                        new HexDecoder(
                          new AuthenticatedDecryptionFilter(decryption,
                                new FileSink(destFile.c_str()))));
            }
            else if (operationMode == "GCM")
            {
                GCM<AES>::Decryption decryption;
                decryption.SetKeyWithIV(key, key.size(), iv, sizeof(iv));

                FileSource fs(sourceFile.c_str(), true,
                            new HexDecoder(
                              new AuthenticatedDecryptionFilter(decryption,
                                    new FileSink(destFile.c_str()))));
            }
        }

        cout << "\nDecrpyted file successfully\n";
    }
    catch (DefaultDecryptor::KeyBadErr)
    {
        cerr << "\nInvalid password or keystore file\nTerminating\n";
        return;
    }
}
