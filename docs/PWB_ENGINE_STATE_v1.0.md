# PWB Engine State v1.0

## Project Status

The Personal Wellness Blueprint (PWB) engine is functionally complete
through the product recommendation layer. The architecture is
explainable: every recommendation can be traced from questionnaire
responses to mechanisms, bioactives, foods and products.

**Current milestone:** Explainable Recommendation Engine

## Architecture

Assessment (58 questions) → HealthProfileBuilder → PriorityEngine →
MechanismResolver → FoodResolver → BioactiveResolver → ProductResolver

Each resolver is independently testable and produces auditable outputs.

## Core Engine Classes

  Class                   Purpose
  ----------------------- ----------------------------------------------
  JsonLoader              Loads taxonomy JSON files
  TestAssessmentFactory   Loads persona JSON assessments
  HealthProfileBuilder    Builds body-system profile
  PriorityEngine          Ranks body systems
  MechanismResolver       Converts priorities into clinical mechanisms
  FoodResolver            Ranks foods from mechanisms
  BioactiveResolver       Ranks bioactives from mechanisms
  ProductResolver         Ranks products from bioactives

## Knowledgebase

### Canonical Taxonomies

-   questions.json
-   body-systems.json
-   mechanisms.json
-   foods.json
-   bioactives.json
-   products.json

### Relationship Matrices

-   body-systems-mechanisms.json
-   food-mechanisms.json
-   bioactive-mechanisms.json
-   products-bioactives.json
-   products-vitamins-minerals.json

## Testing Tools

### Assessment

-   test-assessment-loader.php
-   test-health-profile.php
-   test-health-profile-minimum.php
-   test-health-profile-maximum.php

### Clinical

-   test-priority-engine.php
-   test-mechanism-resolver.php

### Nutrition

-   test-food-resolver.php
-   test-food-explain.php

### Bioactives

-   test-bioactive-resolver.php
-   test-bioactive-explain.php

### Products

-   test-product-resolver.php
-   test-product-explain.php

## Explainability Chain

Every recommendation is traceable:

Questions → Body Systems → Mechanisms → Bioactives → Foods / Products

No resolver contains hard-coded clinical logic; all recommendations are
driven by taxonomy relationships.

## Personas

Implemented persona testing uses JSON files located in:

knowledgebase/personas/

Examples:

-   respiratory-inflammation.json
-   digestive-issues.json
-   neutral.json

Additional personas should be created only as JSON, never as PHP.

## Scoring Model

### Mechanism

Body-system priority determines mechanism clinical score.

### Food

Food Score = Σ(Mechanism Score × Strength/5 × Confidence)

### Bioactive

Bioactive Score = Σ(Mechanism Score × Strength/5 × Confidence)

### Product

Product Score = Σ(Bioactive Score × Status Weight)

Status weighting:

  Status                         Weight
  ---------------------------- --------
  DIRECT                           1.00
  CONFIRMED_PRODUCT_SPECIFIC       1.00
  CONFIRMED_PRODUCT_PROFILE        0.95
  CONFIRMED                        0.90
  SOURCE_DEFINED                   0.75

## Design Decisions

-   JSON taxonomy is the single source of truth.
-   Resolver classes contain no biological knowledge.
-   Clinical evidence lives only inside relationship matrices.
-   One relationship = one JSON row for bioactive-mechanism mappings.
-   Products resolve from bioactives, not directly from mechanisms.

## Outstanding Roadmap

### High Priority

-   Complete remaining bioactive→mechanism mappings
-   Add mechanism evidence levels and publication references
-   Curate all product bioactive relationships

### Medium Priority

-   Dosage weighting
-   Contraindication engine
-   Lifestyle recommendation resolver

### Future

-   Recommendation API
-   PDF report generation
-   Web dashboard integration
-   Versioned taxonomy releases

## Continuity Protocol

For every major milestone:

1.  Commit to Git.
2.  Update this document.
3.  Start new chats from the latest repository or this file.

This document is the canonical project handover and should remain
synchronized with the Git repository.
