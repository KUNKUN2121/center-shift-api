erDiagram
    users {
        bigint id PK
        string name "名前"
        string email "メールアドレス"
        string password "パスワード"
        string role "権限(admin/user)"
        datetime created_at
        datetime updated_at
    }

    shift_periods {
        bigint id PK
        int year "年"
        int month "月"
        datetime start_date "募集開始"
        datetime end_date "募集終了"
        string status "状態(preparing/open/closed/published)"
        datetime created_at
        datetime updated_at
    }

    submissions {
        bigint id PK
        bigint user_id FK "誰の希望か"
        bigint shift_period_id FK "どの期間か"
        datetime start_datetime "希望開始日時"
        datetime end_datetime "希望終了日時"

        text notes "備考"
        datetime created_at
        datetime updated_at
    }

    shifts {
        bigint id PK
        bigint user_id FK "誰のシフトか"
        bigint shift_period_id FK "どの期間か"
        datetime start_datetime "確定開始日時"
        datetime end_datetime "確定終了日時"
        text notes "管理者用メモ"
        datetime created_at
        datetime updated_at
    }

    %% リレーション定義
    users ||--o{ submissions : ""
    shift_periods ||--o{ submissions : ""
    
    users ||--o{ shifts : ""
    shift_periods ||--o{ shifts : ""
