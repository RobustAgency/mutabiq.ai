# Mutabiq.AI Postman Collection

## Overview
This Postman collection provides comprehensive API documentation for the Mutabiq.AI platform, covering authentication, user management, organizations, projects, AI models, compliance, and more.

## Getting Started

### 1. Import the Collection
1. Open Postman
2. Click **Import** button
3. Select the `postman_collection.json` file
4. The collection will be imported with all endpoints

### 2. Configure Environment Variables

Create a new environment in Postman with the following variables:

| Variable | Description | Example Value |
|----------|-------------|---------------|
| `base_url` | Base API URL | `http://localhost` or `https://api.mutabiq.ai` |
| `access_token` | Bearer token for authentication | (automatically set after login) |

### 3. Authentication Flow

1. **Login**: Use the `Authentication > Login` endpoint
   - Provide your email and password
   - The access token will be automatically saved to the environment
2. All subsequent requests will use this token automatically via Bearer Authentication

## Collection Structure

### 📁 Authentication
- **Login**: Authenticate with email/password
- **Accept Invitation**: Create account from invitation token

### 📁 User Profile
- **Get Profile**: Retrieve authenticated user's profile

### 📁 Billing & Subscriptions
- **Get Plans**: List all available subscription plans
- **Subscribe to Plan**: Create, upgrade, or downgrade subscription
- **Cancel Subscription**: Cancel active subscription
- **Get Invoices**: Retrieve all invoices
- **Get Upcoming Invoice**: View next billing invoice
- **Add Payment Method**: Get Stripe portal URL for payment methods

### 📁 Organizations
- **List Organizations**: Get user's organizations with members
- **Create Organization**: Create new organization (requires permission)

### 📁 Team Members
- **List Members**: Get all organization members
- **Update Member**: Update member name and role
- **Delete Member**: Remove member from organization
- **Invite Members**: Send invitations to new members

### 📁 Frameworks
- **List Frameworks**: Browse published frameworks with search/filter
- **Get Framework Details**: View specific framework information

### 📁 Projects
- **List Projects**: Get filtered projects list
- **Create Project**: Create new project
- **Get Project Details**: View specific project
- **Add Member to Project**: Assign user to project with role
- **Add Framework to Project**: Link framework to project

### 📁 AI Models
- **List AI Models**: Get organization's AI models
- **Create AI Model**: Register new AI model
- **Get AI Model Details**: View specific AI model

### 📁 AI Model Versions
- **List AI Model Versions**: Get filtered AI model versions
- **Create AI Model Version**: Create new version of AI model
- **Get AI Model Version Details**: View specific version
- **Update AI Model Version**: Update version information

### 📁 AI Model Cards
- **List AI Model Cards**: Get paginated model cards
- **Create AI Model Card**: Document model ethics and compliance
- **Get AI Model Card Details**: View specific model card
- **Update AI Model Card**: Update model documentation

## Important Enums Reference

### 9. Use Cases
Management of AI implementation use cases including business objectives, ROI tracking, and risk assessments.

| Endpoint | Method | Description | Auth Required |
|----------|--------|-------------|---------------|
| List Use Cases | GET | Get filtered list of use cases | ✅ |
| Create Use Case | POST | Create new use case | ✅ |
| Get Use Case Details | GET | Get specific use case | ✅ |

**Key Features:**
- Comprehensive business case tracking
- ROI and risk assessments
- Data readiness evaluation
- Stakeholder management
- Implementation tracking

### 10. AI Model Use Cases
Association management between AI models and business use cases.

| Endpoint | Method | Description | Auth Required |
|----------|--------|-------------|---------------|
| List AI Model Use Cases | GET | Get filtered associations | ✅ |
| Create AI Model Use Case Association | POST | Link model to use case | ✅ |
| Get AI Model Use Case Details | GET | Get association details | ✅ |
| Update AI Model Use Case | POST | Update association | ✅ |
| Delete AI Model Use Case | DELETE | Remove association | ✅ |

**Key Features:**
- Model-use case relationship tracking
- Version-specific associations
- Relationship type classification (primary, secondary, experimental, backup)

---

## 📊 Total Endpoints Summary

- **Authentication**: 2 endpoints
- **User Profile**: 1 endpoint
- **Billing & Subscriptions**: 6 endpoints
- **Organizations**: 2 endpoints
- **Team Members**: 4 endpoints
- **Frameworks**: 2 endpoints
- **Projects**: 5 endpoints
- **AI Models**: 3 endpoints
- **AI Model Versions**: 4 endpoints
- **AI Model Cards**: 4 endpoints
- **Use Cases**: 3 endpoints
- **AI Model Use Cases**: 5 endpoints

**Total: 41 Endpoints**

---

## 📝 Enum Reference

### User Roles
```
- super_admin
- admin
- owner
- project_lead
- reviewer
- contributor
- auditor
```

### Project Roles
```
- owner
- editor
- reviewer
- auditor
```

### Governance Pillars
```
- ai_governance
- data_governance
- privacy_pdpl
```

### AI Model - Primary Categories
```
- traditional_ml
- deep_learning
- generative_ai
- ai_agents
- specialized_ai
- foundation_models
- multimodal_ai
```

### AI Model - Operational Status
```
- not_deployed
- development
- testing
- production
```

### AI Model - Business Status
```
- planned
- active
- deprecated
- retired
```

### AI Model - Ownership Types
```
- internal
- external
- joint
- licensed
- open_source
- saas
```

### AI Model - Development Sources
```
- internal_development
- external_vendor
- open_source_community
- cloud_provider
- partnership
```

### AI Model Version - Version Types
```
- major
- minor
- patch
- experimental
```

### AI Model Version - Complexity Levels
```
- simple
- moderate
- complex
- massive
```

### AI Model Version - Deployment Status
```
- not_deployed
- deploying
- deployed
- failed
- rollback
```

### AI Model Version - Lifecycle Stages
```
- development
- testing
- staging
- production
- deprecated
- retired
```

### AI Model Version - Input Modalities
```
- text
- image
- audio
- video
- structured_data
- time_series
```

### AI Model Version - Output Modalities
```
- text
- image
- audio
- classification
- regression
- embedding
- structured_data
```

### AI Model Card - Creator Roles
```
- internal_team
- vendor_provided
- community_contributed
- auto_generated
```

### AI Model Card - Formats
```
- standard
- regulatory
- industry_specific
- custom
```

### AI Model Card - Status
```
- draft
- in_review
- approved
- published
- archived
```

### AI Model Card - Publication Status
```
- not_published
- published_internal
- published_public
```

### Relationship Type (AI Model Use Case)
```
- primary
- secondary
- experimental
- backup
```

### Business Domain (Use Case)
```
- customer_service
- fraud_detection
- marketing
- operations
- risk_management
- hr
- finance
- legal
- product_development
- supply_chain
```

### Use Case Status
```
- draft
- under_review
- approved
- in_development
- testing
- staging
- active
- suspended
- deprecated
```

### ROI Classification
```
- High
- Medium
- Low
```

### Priority
```
- High
- Medium
- Low
```

### Risk Level (Use Case)
```
- low
- medium
- high
- critical
```

### Data Sensitivity
```
- public
- internal
- confidential
- restricted
```

### Data Availability Status
```
- available
- partially available
- not available
```

### Data Readiness
```
- D1
- D2
- D3
- D4
```

## Common Response Format

All API responses follow this structure:

```json
{
  "error": false,
  "message": "Success message",
  "data": { /* Response data */ }
}
```

### Error Response
```json
{
  "error": true,
  "message": "Error description",
  "data": null
}
```

## Status Codes

- `200 OK`: Successful GET request
- `201 Created`: Successful POST request creating resource
- `400 Bad Request`: Validation error or bad input
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: Insufficient permissions or inactive organization
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed

## Testing Tips

### 1. Use Variables
- Store IDs in variables after creation: `pm.environment.set('project_id', jsonData.data.id)`
- Reference variables in URLs: `{{base_url}}/api/projects/{{project_id}}`

### 2. Test Scripts
The Login endpoint includes a test script that automatically saves the access token:
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.data && jsonData.data.access_token) {
        pm.environment.set('access_token', jsonData.data.access_token);
    }
}
```

### 3. Sequential Testing
Test endpoints in this order for best results:
1. Login
2. Create Organization
3. Invite Members
4. Create Project
5. Create AI Model
6. Continue with other endpoints

## API Features

### Pagination
Many list endpoints support pagination via query parameters:
- `per_page`: Number of results per page (typically 1-100)
- `page`: Page number (default: 1)

### Filtering
List endpoints often support filtering:
- `name`: Filter by name (partial match)
- `search`: General search term
- `category`: Filter by category
- Custom filters per endpoint

### Authentication
Most endpoints require Bearer token authentication:
```
Authorization: Bearer {access_token}
```

The collection is configured to automatically use the `{{access_token}}` variable.

## Notes

1. **Organization Requirement**: Many endpoints require the user to belong to an organization
2. **Permissions**: Some endpoints check user roles and permissions
3. **Inactive Organizations**: Users from inactive organizations cannot log in
4. **Payment Methods**: Subscription requires adding payment method first
5. **Unique Constraints**: Some fields like organization website and phone must be unique

## Development vs Production

### Local Development
```
base_url: http://localhost
```

### Production
```
base_url: https://api.mutabiq.ai
```

## Support

For API issues or questions:
- Check the inline documentation for each endpoint
- Review validation rules in request descriptions
- Ensure proper authentication and permissions

## Version
- Collection Version: 1.0.0
- API Version: Compatible with current Laravel API
- Last Updated: 2025-11-04

## Next Steps

This collection currently includes:
- ✅ Authentication endpoints
- ✅ User profile
- ✅ Billing & subscriptions
- ✅ Organizations
- ✅ Team members
- ✅ Frameworks
- ✅ Projects
- ✅ AI Models
- ✅ AI Model Versions
- ✅ AI Model Cards

Additional endpoints to be added:
- Use Cases
- AI Model Use Cases
- Stakeholders
- Data Sources & Datasets
- Data Elements
- User Consents
- Vendors & Agreements
- AI Assets & Incidents
- And more...

Stay tuned for updates!
