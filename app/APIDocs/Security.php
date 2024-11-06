<?php
/**
 *  @OA\SecurityScheme(
 *     type="http",
 *     description="Login to 前台-使用者  get the authentication token",
 *     name="authentication",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="webAuth",
 *  ),
 *  @OA\SecurityScheme(
 *     type="http",
 *     description="Login to 後台-管理者 get the authentication token",
 *     name="authentication",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="adminAuth",
 *  ),
 *  @OA\SecurityScheme(
 *     type="http",
 *     description="Login to 商家後台-管理者 get the authentication token",
 *     name="authentication",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="vendorAuth",
 *  ),
 */
