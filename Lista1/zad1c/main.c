#include <openssl/conf.h>
#include <openssl/evp.h>
#include <openssl/err.h>
#include <openssl/bio.h>
#include <openssl/buffer.h>
#include <pthread.h>
#include <string.h>
#include <sys/time.h>

typedef struct
{
    unsigned char * from;
    unsigned char * to;
    unsigned char * sufix;
    unsigned char * iv;
    unsigned char * cipher;
    int cipher_length;
} calculateParams;

void *calculate(void *args);

size_t calcDecodeLength(const char* b64input)
{
    size_t len = strlen(b64input),
        padding = 0;

    if (b64input[len-1] == '=' && b64input[len-2] == '=')
        padding = 2;
    else if (b64input[len-1] == '=')
        padding = 1;

    return (len*3)/4 - padding;
}

int main (void)
{
    unsigned char * sufix = (unsigned char *)"344e00e52d68600ad4977459ea156a0d5bb4d067485d3c140ed75c2";
    unsigned char * iv_string = (unsigned char *)"a86e5f8ff9fe8fb715146732571cb4ea";
    unsigned char * cipher_string = (unsigned char *)"iGYH9hVmvSqST+81rSlLQA+YkEoAVV3fDF8IK7niJvctm9UyWag9VZ7DGJJSAIPeSKq19jkrcbfigDu3Onw5SA==";

    int cvt[UCHAR_MAX+1] = {0};
    cvt['0'] = 0;
    cvt['1'] = 1;
    cvt['2'] = 2;
    cvt['3'] = 3;
    cvt['4'] = 4;
    cvt['5'] = 5;
    cvt['6'] = 6;
    cvt['7'] = 7;
    cvt['8'] = 8;
    cvt['9'] = 9;
    cvt['a'] = 10;
    cvt['b'] = 11;
    cvt['c'] = 12;
    cvt['d'] = 13;
    cvt['e'] = 14;
    cvt['f'] = 15;

    unsigned char iv[16];
    for (int i = 0; i < 16; ++i)
    {
        iv[i] = 16 * cvt[(unsigned char)iv_string[2 * i]] +
                     cvt[(unsigned char)iv_string[2 * i + 1]];
    }

    BIO *bio, *b64;
    unsigned char* cipher;

    ERR_load_crypto_strings();
    OpenSSL_add_all_algorithms();
    OPENSSL_config(NULL);

    int cipher_length = calcDecodeLength(cipher_string);
    cipher = (unsigned char*)malloc(cipher_length + 1);
    (cipher)[cipher_length] = '\0';

    bio = BIO_new_mem_buf(cipher_string, -1);
    b64 = BIO_new(BIO_f_base64());
    bio = BIO_push(b64, bio);

    BIO_set_flags(bio, BIO_FLAGS_BASE64_NO_NL);
    BIO_read(bio, cipher, strlen(cipher_string));
    BIO_free_all(bio);

    pthread_t thread[4];
    unsigned char * from[4] = {
         (unsigned char *)"000000000",
         (unsigned char *)"400000001",
         (unsigned char *)"800000001",
         (unsigned char *)"c00000001"
    };

    unsigned char * to[4] = {
         (unsigned char *)"400000000",
         (unsigned char *)"800000000",
         (unsigned char *)"c00000000",
         (unsigned char *)"fffffffff"
    };

    for (int i = 0; i < 4; ++i)
    {
        calculateParams * params = malloc(sizeof *params);
        params->from = from[i];
        params->to = to[i];
        params->iv = iv;
        params->sufix = sufix;
        params->cipher = cipher;
        params->cipher_length = cipher_length;
        pthread_create(&thread[i], NULL, calculate, params);
    }

    struct timeval tv1, tv2;
    gettimeofday(&tv1, NULL);
    for (int i = 0; i < 4; ++i)
    {
        pthread_join(thread[i], NULL);
    }
    gettimeofday(&tv2, NULL);
    printf ("Total time = %f seconds\n",
             (double) (tv2.tv_usec - tv1.tv_usec) / 1000000 +
             (double) (tv2.tv_sec - tv1.tv_sec));
    EVP_cleanup();
    ERR_free_strings();

    return 0;
}

void* calculate(void * args)
{
    calculateParams * params = args;

    char * fileName = (char*)malloc(11  + strlen(params->from) + strlen(params->to));
    strcpy(fileName, "from");
    strcat(fileName, params->from);
    strcat(fileName, "to");
    strcat(fileName, params->to);
    strcat(fileName, ".txt");
    FILE *f = fopen(fileName, "w");
    free(fileName);

    EVP_CIPHER_CTX *ctx;
    ctx = EVP_CIPHER_CTX_new();

    int length = strlen(params->from) - 1;

    unsigned char * key_string = (unsigned char *) malloc(1 + strlen(params->from)+ strlen(params->sufix));
    strcpy(key_string, params->from);
    strcat(key_string, params->sufix);

    int cvt[UCHAR_MAX+1] = {0};
    cvt['0'] = 0;
    cvt['1'] = 1;
    cvt['2'] = 2;
    cvt['3'] = 3;
    cvt['4'] = 4;
    cvt['5'] = 5;
    cvt['6'] = 6;
    cvt['7'] = 7;
    cvt['8'] = 8;
    cvt['9'] = 9;
    cvt['a'] = 10;
    cvt['b'] = 11;
    cvt['c'] = 12;
    cvt['d'] = 13;
    cvt['e'] = 14;
    cvt['f'] = 15;

    unsigned char key[32];
    for (int i = 0; i < 32; i++) {
        key[i] = 16 * cvt[(unsigned char)key_string[2*i]] +
                      cvt[(unsigned char)key_string[2*i + 1]];
    }

    unsigned char * prefix = (unsigned char*)malloc(1 + strlen(params->from));
    strcpy(prefix, params->from);

    while (1)
    {
        unsigned char decryptedtext[128];
        int decryptedtext_len;
        int len;
        EVP_DecryptInit_ex(ctx, EVP_aes_256_cbc(), NULL, key, params->iv);
        EVP_DecryptUpdate(ctx, decryptedtext, &len, params->cipher, params->cipher_length);
        decryptedtext_len = len;
        EVP_DecryptFinal_ex(ctx, decryptedtext + len, &len);
        decryptedtext_len += len;

        decryptedtext[decryptedtext_len] = '\0';

        int found = 1;

        int c,ix,n,j;
        for (int i = 0, ix= decryptedtext_len; i < ix; i++)
        {
            c = (unsigned char) decryptedtext[i];
            if (0x00 <= c && c <= 0x7f) n=0;
            else if ((c & 0xE0) == 0xC0) n=1;
            else if ( c==0xed && i<(ix-1) && ((unsigned char)decryptedtext[i+1] & 0xa0)==0xa0)
            {
                found = 0;
                break;
            }
            else if ((c & 0xF0) == 0xE0) n=2;
            else if ((c & 0xF8) == 0xF0) n=3;
            else
            {
                found = 0;
                break;
            }
            for (j=0; j<n && i<ix; j++) {
                if ((++i == ix) || (( (unsigned char)decryptedtext[i] & 0xC0) != 0x80))
                {
                    found = 0;
                    break;
                }
            }
        }

        if (found == 1)
            fprintf(f, "%s key: %s\n", decryptedtext, key_string);

        if (strcmp(prefix, params->to) == 0)
            break;

        int iter = length;
        while (iter >= 0)
        {
            if (++prefix[iter] == ':')
                prefix[iter] = 'a';
            else if (prefix[iter] == 'g')
            {
                prefix[iter] = '0';
                --iter;
                continue;
            }
            break;
        }

        for (int i = length; i >= iter; --i)
            key_string[i] = prefix[i];

        for (int i = 0; i < length; i++)
            key[i] = 16 * cvt[(unsigned char)key_string[2*i]] +
                          cvt[(unsigned char)key_string[2*i + 1]];

    }

    EVP_CIPHER_CTX_free(ctx);
    fclose(f);
    free(params);
    free(key_string);
    free(prefix);
}
