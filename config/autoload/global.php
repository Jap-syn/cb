<?php
/*
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

 return array(
         // ------------------------------
         // DB接続時に、システム共通のタイムゾーン設定を行います。
         // 無効にする場合は、コメントアウトしてください。
         // ex) '+9:00'、'Asia/Tokyo'
         // ------------------------------
         'RDS_SESSION_TIMEZONE' => '+9:00',  // 無効にする場合は、コメントアウトしてください
 );

