# Data Anonymization in eQual

Data anonymization is the process of transforming personal data to make it non-identifiable. This ensures that individuals or entities can no longer be identified, either directly or indirectly. 

This process is crucial for compliance with modern data protection regulations such as GDPR, CCPA, LGPD, PIPEDA, and PDPA. It also ensures the protection of individuals' privacy while allowing the use of data for analytical or commercial purposes without violating privacy rules.

---

## Why Anonymize Data?

Anonymizing data offers several benefits:

1. **Data Security**: Anonymized data significantly reduces the risk of data leaks or malicious exploitation, as the information can no longer be attributed to specific individuals.
2. **Regulatory Compliance**: Ensures adherence to privacy laws and regulations, avoiding legal penalties.
3. **Data Reuse**: Enables the reuse of data for purposes such as statistical analysis or research without breaching data protection rules. This is particularly useful in sensitive sectors like healthcare.
4. **Customer Trust**: Strengthens trust and improves reputation by demonstrating a commitment to data protection.
5. **Analytical Flexibility**: Allows companies to leverage anonymized data for market studies, behavioral analysis, and other research while respecting individual rights.

---

## How eQual Handles Data Anonymization

In eQual, data anonymization is **irreversible** and primarily based on **randomization**. Data values are modified randomly using datasets selected based on each field's descriptor, leveraging its type, usage (when available), and, if applicable, its name.

eQual supports two main approaches to anonymization:
1. **Object-Level Anonymization**: Specific objects of a given entity can be anonymized, filtered using a domain.
2. **Package-Level Anonymization**: All objects of a given package can be anonymized using a schema to specify the strategies for anonymizing the data.

---

## Entity Schema for Anonymization

At the level of field descriptors (as defined in [`getColumns()`](./entities/entities.md#defining-columns)), certain attributes define the behavior of anonymization:

### Key Attributes

- **`sensitive`**:  
  When set to `true`, the field is always anonymized, even if it is not explicitly listed among the fields to anonymize.  
  Example:
  ```php
  public static function getColumns() {
      return [
          'content' => [
              'type'        => 'string',
              'description' => 'Content of the message.',
              'sensitive'   => true
          ]
      ];
  }
  ```

- **`generation`**:  
  Specifies how the field's values should be generated during anonymization. This must refer to a static method of the targeted entity (without parameters).

## Anonymization Schema

An anonymization schema is a JSON array where each object specifies a model to be anonymized, including the fields and relationships to be processed.

### Example Schema

```json
[
    {
        "name": "core\\alert\\MessageModel",
        "fields": ["label", "description"],
        "relations": {
            "messages_ids": {
                "fields": ["label", "description"]
            }
        }
    }
]
```

### Schema Attributes

| **Attribute**   | **Description**                                                                      |
| --------------- | ------------------------------------------------------------------------------------ |
| **`name`**      | Specifies the fully qualified name of the model class to be anonymized. *(required)* |
| **`fields`**    | Lists the [fields](./entities/entities.md#defining-fields) that need to be anonymized.                                 |
| **`relations`** | Defines relationships to other models and how to handle them.                        |

---

## Best Practices for Data Anonymization

1. **Identify Sensitive Data**: Use the `sensitive` attribute to mark fields that must always be anonymized.
2. **Define Clear Schemas**: Create well-structured anonymization schemas to ensure consistency and clarity.
3. **Test Anonymization**: Validate the anonymization process to ensure compliance with privacy regulations and data integrity.
4. **Document Relationships**: Clearly define how relationships between models should be anonymized to avoid data inconsistencies.

---

## Additional Notes

- Data anonymization in eQual is **irreversible**, meaning the original data cannot be restored once anonymized.
- The `generation` attribute allows for custom anonymization logic, making the process flexible and adaptable to specific needs.
- For more information on how to implement anonymization in your project, refer to the [anonymize your objects](./data-anonymization.md).

---
