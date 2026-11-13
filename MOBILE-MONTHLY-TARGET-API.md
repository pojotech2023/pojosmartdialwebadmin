# Mobile telecaller monthly target API

The mobile login response includes a `data.monthly_target` summary for the
authenticated telecaller. The same summary can be refreshed from the dedicated
endpoint below. Both return the current calendar month's data for the signed-in
telecaller only; the API does not accept an employee ID to select another user.

## Login response

```http
POST /api/login
Content-Type: application/json

{
  "mobile": "9876543210",
  "password": "your-password"
}
```

For a telecaller, the successful response includes:

```json
{
  "status": true,
  "follow_up_token": "<token>",
  "data": {
    "emp_id": 12,
    "name": "Telecaller Name",
    "monthly_target": {
      "emp_id": 12,
      "employee_name": "Telecaller Name",
      "month": "2026-11",
      "month_label": "November 2026",
      "assigned_customer_count": 8,
      "monthly_target": 100000,
      "archived_amount": 25000,
      "balance_to_achieve": 75000,
      "currency": "INR"
    }
  }
}
```

`monthly_target` is the configured monthly amount. `archived_amount` sums the
prices of leads confirmed during the current month. `balance_to_achieve` is the
non-negative amount left to meet the target. `assigned_customer_count` counts
leads assigned during the current month. Amounts are in INR.

Non-telecaller login responses set `data.monthly_target` to `null`.

## Refresh the summary

```http
GET /api/telecaller/monthly-target
Authorization: Bearer <follow_up_token>
Accept: application/json
```

Success response:

```json
{
  "status": true,
  "data": {
    "emp_id": 12,
    "employee_name": "Telecaller Name",
    "month": "2026-11",
    "month_label": "November 2026",
    "assigned_customer_count": 8,
    "monthly_target": 100000,
    "archived_amount": 25000,
    "balance_to_achieve": 75000,
    "currency": "INR"
  }
}
```

The endpoint returns `401` if the token is missing, invalid, or expired, and
`403` if the token belongs to a non-telecaller.
