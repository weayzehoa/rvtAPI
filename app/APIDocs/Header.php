<?php
/**
 *  @OA\Info(
 *      version="1.0.0",
 *      title="iCarry.me API 文件",
 *     description="iCarry.me API 文件.
1. 同一個client連線API預設限制60次/分鐘，超過則會出現Too Many Requests(429)被限制10秒鐘無法訪問API。
2. API右邊若有鎖頭代表需要輸入 Authorization 來使用，請找到相對應(使用者、管理者、商家)的API登入取得Token。
3. 購物車 API 使用客戶端 uuid 作驗證，可不用輸入 Authorization ，若使用者登入後有 Authorization 則購物車資料將依照 uuid 驗證，全部轉為該使用者。
4. Authorization 過期前若干時間將會自動更並附加到 header 中，請隨時檢查 Authorization 是否有更新。
5. API Query 中 header 中若帶有 Authorization 資料，將會返回原來的 Authorization 。 (系統性錯誤則不返回)
6. 合作廠商連接若未使用登入方式取得 Authorization ，則需使用 partner_id 、 verify 及 icarry_uid 來驗證。
7. 注意事項: 所有參數資料都必須經過urlencode編碼，否則會造成特殊符號被忽略。",
 *     @OA\Contact(
 *         email="roger@icarry.me"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 *  )
 *
 *  @OA\Server(
 *      url="",
 *      description="目前伺服器"
 *  )
 *
 *  @OA\Server(
 *      url="https://beta.api.icarry.me",
 *      description="iCarry開發用測試機"
 *  )
 */
