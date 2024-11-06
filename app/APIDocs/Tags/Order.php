<?php
    /**
     * @OA\Get(
     *     path="/web/v1/order",
     *     operationId="webOrderList",
     *     tags={"訂單"},
     *     summary="前台-使用者訂單資料列表",
     *     description="1. 必須登入帶 Authorization Header，合作廠商未使用登入方式則須帶 partner_id、verify、icarry_uid 參數。
2. lang 參數 取得相對應語言資料。
3. 訂單狀態代碼說明 (-1 已刪除[前台不顯示訂單]、0 尚未付款、1 已付款、2 集貨中、3 已出貨、4 已完成)",
     *     @OA\Parameter(
     *         name="keyword",
     *         description="關鍵字(訂單編號、收件人或商品名稱)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="鳳凰酥",
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         description="語言代號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="英文-en", value="en", summary="英文語言代號-en"),
     *         @OA\Examples(example="日文-jp", value="jp", summary="日文語言代號-jp"),
     *         @OA\Examples(example="韓文-kr", value="kr", summary="韓文語言代號-kr"),
     *         @OA\Examples(example="泰文-th", value="th", summary="泰文語言代號-th"),
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得使用者訂單資料列表",
     *         @OA\JsonContent(ref="#/components/schemas/OrderListSuccess")
     *     ),
     *     security={{"webAuth": {}}}
    * )
    */
    /**
     * @OA\Get(
     *     path="/web/v1/order/{id}",
     *     operationId="webOrderShow",
     *     tags={"訂單"},
     *     summary="前台-使用者訂單資料",
     *     description="1. 必須登入帶 Authorization Header，合作廠商未使用登入方式則須帶 partner_id、verify、icarry_uid 參數。
2. lang 參數 取得相對應語言資料。
3. 訂單狀態代碼說明 (-1 已刪除[前台不顯示訂單]、0 尚未付款、1 已付款、2 集貨中、3 已出貨、4 已完成)",
     *     @OA\Parameter(
     *         name="id",
     *         description="訂單id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         description="語言代號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="英文-en", value="en", summary="英文語言代號-en"),
     *         @OA\Examples(example="日文-jp", value="jp", summary="日文語言代號-jp"),
     *         @OA\Examples(example="韓文-kr", value="kr", summary="韓文語言代號-kr"),
     *         @OA\Examples(example="泰文-th", value="th", summary="泰文語言代號-th"),
     *     ),
     *     @OA\Response(response=200,
     *         description="Success, 取得使用者訂單資料。",
     *         @OA\JsonContent(ref="#/components/schemas/OrderShowSuccess")
     *     ),
     *     security={{"webAuth": {}}}
     * )
    */
    /**
     * @OA\Post(
     *     path="/web/v1/order",
     *     operationId="webOrderStore",
     *     tags={"訂單"},
     *     summary="前台-使用者訂單新增",
     *     description="前台-訂單新增 [(流程圖)](/flowchart/webOrder_Post.png)
1. 必須登入帶 Authorization Header，合作廠商未使用登入方式則須帶 partner_id、verify、icarry_uid 參數。(測試機已綁定 使用者id 84533 忽略 Authorization。)
2. domain 必填， iCarry.me 官網請填 icarry.me，合作廠商請填來源 domain。
3. from_country_id (發貨國家id，目前僅提供 1 台灣發貨 5 日本發貨 ) 與 to_country_id (目的地國家id) 必填，請先從 國家資料API 取得相對應的國家資料。
4. shipping_method_id (寄送方式id) 必填，請先從 寄送方式API 取得相對應的寄送方式資料。
5. pay_method (付款方式) 必填，請先從 付款方式API 取得相對應啟用的付款方式資料，合作廠商免填。
6. create_type 必填， 若為 icarry 官網請填 web， icarry APP 請填 app， 合作廠商請填自己的名稱 ex: 17Life。
7. buyer_name (購買者姓名） 與 buyer_email (購買者電子郵件) 必填，請先從 使用者API 取得相對應的使用者資料。
8. invoice_sub_type (發票資訊) 必填， 1 捐贈 2 個人 3 公司。
9. 當 to_country_id = 2 (中國), agree 必填， 同意中國實名制收件人需上傳身分證正反面。當 to_country_id = 6 (韓國), receiver_birthday 必填，韓國政府要求收件人生日八碼。
10. 當 to_country_id = 1 (台灣), take_time 必填， shipping_method_id 可為 1 (機場提貨), 2 (旅店提貨), 4(指定配送地址)，其餘國家 shipping_method_id 只能為 4。
11. 當 shipping_method_id = 1 (機場提貨)， airport_pickup_location 、 airport_flight_number 、 receiver_name 、 receiver_email 、 receiver_nation_number 、 receiver_phone_number 必填，。
12. 當 shipping_method_id = 2 (旅店提貨)， hotel_name 、 hotel_address 、 receiver_name 、 receiver_email 、 receiver_nation_number 、 receiver_phone_number 必填，。
13. 當 shipping_method_id = 4 (指定配送地點)， user_address_id 必填，請透過 使用者常用地址API 新增使用者常用地址，依照寄送地區不同使用不同的常用地址。
14. 當 invoice_sub_type = 2 (個人)， carrier_type 與 carrier_num 可填選， carrier_type = 1 或 2 時 carrier_num 必填。
15. 當 invoice_sub_type = 3 (公司)， invoice_title 與 invoice_number 必填。
16. 其餘欄位參數選填。
17. 注意：本文件測試付款方式請填 (購物金) 忽略金流流程，否則將產生COSR錯誤訊息。測試金流請至：[https://dev.icarry.me/payTest](https://dev.icarry.me/payTest) 網頁測試。",
     *     @OA\Parameter(
     *         name="domain",
     *         description="domain",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="icarry.me",
     *     ),
     *     @OA\Parameter(
     *         name="from_country_id",
     *         description="商品發貨國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="1",
     *     ),
     *     @OA\Parameter(
     *         name="to_country_id",
     *         description="寄送目的地國家id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="1",
     *     ),
     *     @OA\Parameter(
     *         name="shipping_method_id",
     *         description="寄送方式id",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="4",
     *     ),
     *     @OA\Parameter(
     *         name="take_time",
     *         description="預定取貨日期",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="date"),
     *         example="2021-07-01",
     *     ),
     *     @OA\Parameter(
     *         name="pay_method",
     *         description="付款方式",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="購物金", value="購物金", summary="購物金(僅測試建立訂單，不含金流)"),
     *         @OA\Examples(example="ACpay", value="ACpay", summary="ACpay"),
     *         @OA\Examples(example="智付通信用卡", value="智付通信用卡", summary="智付通信用卡"),
     *         @OA\Examples(example="智付通ATM轉帳", value="智付通ATM", summary="智付通ATM轉帳"),
     *         @OA\Examples(example="智付通超商代碼繳款", value="智付通CVS", summary="智付通超商代碼繳款"),
     *     ),
     *     @OA\Parameter(
     *         name="promotion_code",
     *         description="促銷代碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="VISA",
     *     ),
     *     @OA\Parameter(
     *         name="promotion_code",
     *         description="使用購物金",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="不使用", value="", summary="不使用"),
     *         @OA\Examples(example="使用", value="1", summary="使用"),
     *     ),
     *     @OA\Parameter(
     *         name="create_type",
     *         description="哪邊建立",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="web",
     *     ),
     *     @OA\Parameter(
     *         name="buyer_name",
     *         description="購買者名字",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="web",
     *     ),
     *     @OA\Parameter(
     *         name="buyer_email",
     *         description="購買者email",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="weayzehoa@gmail.com",
     *     ),
     *     @OA\Parameter(
     *         name="invoice_sub_type",
     *         description="發票資訊 1捐贈、2個人、3公司",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="1 捐贈", value="1", summary="1 捐贈"),
     *         @OA\Examples(example="2 個人", value="2", summary="2 個人"),
     *         @OA\Examples(example="3 公司", value="3", summary="3 公司"),
     *     ),
     *     @OA\Parameter(
     *         name="carrier_type",
     *         description="載具類別",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         @OA\Examples(example="不選擇", value="", summary="不選擇"),
     *         @OA\Examples(example="0 手機條碼", value="0", summary="0 手機條碼"),
     *         @OA\Examples(example="1 自然人憑證條碼", value="1", summary="1 自然人憑證條碼"),
     *     ),
     *     @OA\Parameter(
     *         name="carrier_num",
     *         description="手機條碼/自然人憑證條碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="user_address_id",
     *         description="使用者常用地址id",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="34360"
     *     ),
     *     @OA\Parameter(
     *         name="airport_pickup_location",
     *         description="機場提貨地址",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="null", value="", summary="不選擇"),
     *         @OA\Examples(example="桃園機場/第一航廈出境大廳門口", value="桃園機場/第一航廈出境大廳門口", summary="桃園機場/第一航廈出境大廳門口"),
     *         @OA\Examples(example="桃園機場/第二航廈出境大廳門口", value="桃園機場/第二航廈出境大廳門口", summary="桃園機場/第二航廈出境大廳門口"),
     *         @OA\Examples(example="松山機場/第一航廈台灣宅配通（E門旁）", value="松山機場/第一航廈台灣宅配通（E門旁）", summary="松山機場/第一航廈台灣宅配通（E門旁）"),
     *         @OA\Examples(example="花蓮航空站/挪亞方舟旅遊", value="花蓮航空站/挪亞方舟旅遊", summary="花蓮航空站/挪亞方舟旅遊"),
     *         @OA\Examples(example="東京成田機場第一航廈4樓出境大廳南翼", value="東京成田機場第一航廈4樓出境大廳南翼", summary="東京成田機場第一航廈4樓出境大廳南翼"),
     *         @OA\Examples(example="東京成田機場第二航廈3樓出境大廳", value="東京成田機場第二航廈3樓出境大廳", summary="東京成田機場第二航廈3樓出境大廳"),
     *         @OA\Examples(example="東京羽田機場3樓出境大境", value="東京羽田機場3樓出境大境", summary="東京羽田機場3樓出境大境"),
     *     ),
     *     @OA\Parameter(
     *         name="airport_flight_number",
     *         description="飛機航班資料",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="CI-123",
     *     ),
     *     @OA\Parameter(
     *         name="hotel_name",
     *         description="旅店名稱",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="君悅大飯店",
     *     ),
     *     @OA\Parameter(
     *         name="hotel_room_number",
     *         description="旅店房號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="321",
     *     ),
     *     @OA\Parameter(
     *         name="hotel_address",
     *         description="旅店地址",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="信義路XXXXXXXXX",
     *     ),
     *     @OA\Parameter(
     *         name="hotel_checkout_date",
     *         description="旅店退房日期",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="date"),
     *         example="2021-07-05",
     *     ),
     *     @OA\Parameter(
     *         name="receiver_name",
     *         description="收件人名字",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="iCarry",
     *     ),
     *     @OA\Parameter(
     *         name="receiver_email",
     *         description="收件人Email",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="iCarry@icarry.me",
     *     ),
     *     @OA\Parameter(
     *         name="receiver_nation_number",
     *         description="收件人電話國際碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="+886",
     *     ),
     *     @OA\Parameter(
     *         name="receiver_phone_number",
     *         description="收件人電話號碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="906486688",
     *     ),
     *     @OA\Parameter(
     *         name="receiver_other_contact",
     *         description="收件者其他聯絡方式",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="不填寫", value="", summary="不填寫"),
     *         @OA\Examples(example="LINE", value="LINE", summary="LINE"),
     *         @OA\Examples(example="WeChat", value="WeChat", summary="WeChat"),
     *         @OA\Examples(example="WhatsApp", value="WhatsApp", summary="WhatsApp"),
     *         @OA\Examples(example="電話號碼", value="電話號碼", summary="電話號碼"),
     *     ),
     *     @OA\Parameter(
     *         name="receiver_other_contact",
     *         description="其他聯絡方式ID/號碼",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="invoice_title",
     *         description="發票抬頭",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="公司名稱",
     *     ),
     *     @OA\Parameter(
     *         name="invoice_number",
     *         description="統一編號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="12331456",
     *     ),
     *     @OA\Parameter(
     *         name="agree",
     *         description="同意中國政策",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         example="1"
     *     ),
     *     @OA\Parameter(
     *         name="asiamiles_account",
     *         description="亞洲萬里通帳號",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="user_memo",
     *         description="使用者備註",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="returnURL",
     *         description="金流返回網址",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="https://web.localhost/pay"
     *     ),
     *     @OA\Response(response=200, description="Success, 取得 訂單id (order_id)。"),
     *     @OA\Response(response=400, description="1. Error, appCode 999, 參數錯誤/參數不存在。
2. Error, appCode 0, 建立訂單失敗，沒有可結帳商品。
3. Error, appCode 1, 未勾選同意中國政策規定。
4. Error, appCode 2, 使用者常用地址id錯誤。
5. Error, appCode 3, 付款方式錯誤。
6. Error, appCode 4, 不提供 日本 發貨到 日本 (國家名稱根據 from_country_id 與 to_country_id 自動判斷)。
7. Error, appCode 9, 建立訂單完成，付款失敗。"),
    *     security={{"webAuth": {}}}
    * )
    */


    /**
     * @OA\Patch(
     *     path="/web/v1/order/{id}",
     *     operationId="webOrderUpdate",
     *     tags={"訂單"},
     *     security={{"webAuth": {}}},
     *     deprecated=true,
     *     summary="前台-使用者訂單更新 (重新付款、完成訂單)",
     *     description="必須登入帶 Authorization Header，合作廠商未使用登入方式則須帶 partner_id、verify、icarry_uid 參數。
1. type = repay 重新付款，必須提供付款方式 pay_method，付款方式 value 請透過 付款方式API 取得可用的付款方式資料。
2. type = finished 完成訂單，此為舊站功能。
3. 注意，重新付款可能會導引到金流頁面。",
     *     @OA\Parameter(
     *         name="id",
     *         description="訂單id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         description="類型 (repay or finished)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="不選擇", value="", summary="不選擇"),
     *         @OA\Examples(example="重新付款", value="repay", summary="重新付款"),
     *         @OA\Examples(example="完成訂單", value="finished", summary="完成訂單"),
     *     ),
     *     @OA\Parameter(
     *         name="pay_method",
     *         description="付款方式",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response=200, description="Success, appCode 0, 訂單更新完成。"),
     *     @OA\Response(response=400, description="錯誤代碼說明
1. Error, appCode 999, 參數錯誤/參數不存在。
2. Error, appCode 0, 付款方式錯誤/不存在。
3. Error, appCode 9, 訂單狀態錯誤，無法重新付款。"),
     * )
    */
    /**
     * @OA\DELETE(
     *     path="/web/v1/order/{id}",
     *     operationId="webOrderDelete",
     *     tags={"訂單"},
     *     summary="前台-使用者訂單刪除",
     *     description="1. 必須登入帶 Authorization Header，合作廠商未使用登入方式則須帶 partner_id、verify、icarry_uid 參數。
2. 只有訂單狀態為 0 才可以刪除，其餘狀態只能透過管理者從後台取消。
3. 訂單狀態代碼說明 (-1 已刪除[前台不顯示訂單]、0 尚未付款、1 已付款、2 集貨中、3 已出貨、4 已完成)",
     *     @OA\Parameter(
     *         name="id",
     *         description="訂單id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success, appCode 0, 刪除成功。"),
     *     @OA\Response(response=400, description="Error, appCode 0, 無法刪除。"),
     *     security={{"webAuth": {}}}
     * )
    */
    /**
     * @OA\Get(
     *     path="/web/v1/order/buyAgain/{id}",
     *     operationId="webOrderBuyAgain",
     *     tags={"訂單"},
     *     summary="前台-使用者訂單再買一次",
     *     description="前台-使用者訂單再買一次將訂單商品內重新加入購物車
1. 必須登入帶 Authorization Header，合作廠商未使用登入方式則須帶 partner_id、verify、icarry_uid 參數。
2. domain 為 icarry.me 則 session 及 domain 必填。",
     *     @OA\Parameter(
     *         name="id",
     *         description="訂單id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         description="domain",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *                 example="icarry.me"
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         description="客戶端 session (必填 uuid max:40)",
     *         required=true,
     *         in="query",
     *         @OA\Schema(type="string"),
     *         example="5bc505c5-df1d-4252-9fa8-598d40788b62",
     *     ),
     *     @OA\Response(response=200,
     *          description="1. Success, appCode 0, 已重新加入購物車。結帳前請再次檢查購物車。
2. Warning, appCode 1, 未加入任何商品至購物車，該訂單商品已下架或暫無庫存無法結帳。
3. Warning, appCode 2, 已重新加入購物車，有部分商品已下架或暫無庫存無法結帳。結帳前請再次檢查購物車。"),
     *     security={{"webAuth": {}}}
     * )
    */
