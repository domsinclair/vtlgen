# Database Documentation: {{database}}

{{#tables}}
## Table: {{tableName}}

### Columns

| COLUMN_NAME   | COLUMN_TYPE | IS_NULLABLE | COLUMN_KEY | COLUMN_DEFAULT | EXTRA |
|---------------|-------------|-------------|------------|----------------|-------|
{{#columns}}
| {{columnName}} | {{columnType}} | {{isNullable}} | {{columnKey}} | {{columnDefault}} | {{extra}} |
{{/columns}}

### Indexes

| INDEX_NAME   | COLUMN_NAME | NON_UNIQUE | SEQ_IN_INDEX | COLLATION | CARDINALITY | SUB_PART | PACKED | NULLABLE | INDEX_TYPE | INDEX_COMMENT |
|--------------|-------------|------------|--------------|-----------|-------------|----------|--------|----------|------------|---------------|
{{#indexes}}
| {{indexName}} | {{columnName}} | {{nonUnique}} | {{seqInIndex}} | {{collation}} | {{cardinality}} | {{subPart}} | {{packed}} | {{nullable}} | {{indexType}} | {{indexComment}} |
{{/indexes}}

### Constraints

| CONSTRAINT_NAME   | CONSTRAINT_TYPE |
|-------------------|-----------------|
{{#constraints}}
| {{constraintName}} | {{constraintType}} |
{{/constraints}}

### Foreign Keys

| COLUMN_NAME   | CONSTRAINT_NAME | REFERENCED_TABLE_NAME | REFERENCED_COLUMN_NAME | UPDATE_RULE | DELETE_RULE |
|---------------|-----------------|----------------------|------------------------|-------------|-------------|
{{#foreignKeys}}
| {{columnName}} | {{constraintName}} | {{referencedTableName}} | {{referencedColumnName}} | {{updateRule}} | {{deleteRule}} |
{{/foreignKeys}}

{{/tables}}

{{#views}}
## View: {{viewName}}

### View Definition

```sql
{{viewDefinition}}

