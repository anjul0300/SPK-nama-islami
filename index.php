<?php

session_start();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        SPK Pemilihan Nama Islami
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            min-height: 100vh;
            background: linear-gradient(
                135deg,
                #f8f9fa,
                #e9f7ef
            );
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .title {
            font-weight: 700;
        }

        .subtitle {
            line-height: 1.8;
        }

        .feature {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            background: #ffffff;
        }

        .feature h5 {
            font-weight: 700;
        }

        .start-button {
            padding: 13px 30px;
            font-size: 17px;
            font-weight: 600;
            border-radius: 10px;
        }

    </style>

</head>


<body>


<div class="container hero">


    <div
        class="card hero-card shadow-lg w-100"
    >

        <div class="card-body p-4 p-md-5">


            <!-- ==================================================
                 JUDUL
            ================================================== -->

            <div class="text-center mb-5">

                <h1 class="title">

                    Sistem Pendukung Keputusan
                    Pemilihan Nama-Nama Islami

                </h1>


                <p class="text-muted mt-3 subtitle">

                    Menggunakan metode
                    <strong>
                        Analytical Hierarchy Process (AHP)
                    </strong>
                    untuk membantu pengguna menentukan
                    rekomendasi nama Islami berdasarkan
                    beberapa kriteria.

                </p>

            </div>



            <!-- ==================================================
                 FITUR SISTEM
            ================================================== -->

            <div class="row g-4 mb-5">


                <div class="col-md-4">

                    <div class="feature text-center">

                        <h5>

                            Arti Nama

                        </h5>


                        <p class="text-muted mb-0">

                            Mempertimbangkan nilai atau
                            makna yang terkandung dalam nama.

                        </p>

                    </div>

                </div>



                <div class="col-md-4">

                    <div class="feature text-center">

                        <h5>

                            Tingkat Keunikan

                        </h5>


                        <p class="text-muted mb-0">

                            Mempertimbangkan tingkat
                            keunikan dari nama yang tersedia.

                        </p>

                    </div>

                </div>



                <div class="col-md-4">

                    <div class="feature text-center">

                        <h5>

                            Nilai Keislaman

                        </h5>


                        <p class="text-muted mb-0">

                            Mempertimbangkan tingkat
                            nilai keislaman yang dimiliki nama.

                        </p>

                    </div>

                </div>


            </div>



            <!-- ==================================================
                 INFORMASI ALUR
            ================================================== -->

            <div
                class="alert alert-info text-center mb-4"
            >

                <strong>
                    Bagaimana sistem bekerja?
                </strong>

                <br>

                Anda cukup memilih jenis kelamin,
                kemudian menentukan tingkat kepentingan
                dari tiga kriteria.

                Sistem akan melakukan perhitungan AHP
                secara otomatis dan menampilkan ranking
                nama Islami.

            </div>



            <!-- ==================================================
                 TOMBOL
            ================================================== -->

            <div class="text-center">


                            <a
                href="pilih_jenis_kelamin.php"
                class="btn btn-success start-button"
            >
                Mulai Pemilihan Nama
            </a>


                <div class="mt-3">

                    <a
                        href="login.php"
                        class="text-decoration-none"
                    >

                        Login sebagai Admin

                    </a>

                </div>


            </div>


        </div>

    </div>


</div>


</body>

</html>