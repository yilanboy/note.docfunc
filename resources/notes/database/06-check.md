# Check

在 PostgreSQL 中，可以使用 `CHECK` 來避免某些錯誤的資料被寫入。

以多階段留言系統為例，留言的 `parent_id` 不應該等於自己的 `id` (即自己的父留言不應該是留言自己本身)。我們可以在 PostgreSQL 中使用 `CHECK` 來確認這個規則。

```sql
ALTER TABLE comments
ADD CONSTRAINT parent_id_cannot_be_its_own_id CHECK (comments.id <> comments.parent_id);
```

如果想移除這個 `CONSTRAINT`，可以使用 `DROP` 來移除這個規則。

```sql
ALTER TABLE comments
DROP CONSTRAINT parent_id_cannot_be_its_own_id;
```
